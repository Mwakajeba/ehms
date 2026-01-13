@extends('layouts.main')

@section('title', 'Add Submissions')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Assignments', 'url' => route('school.assignments.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'Add Submissions', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />
        <h6 class="mb-0 text-uppercase">ADD SUBMISSIONS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                <div>
                                    <h5 class="mb-0 text-primary">{{ $assignment->title }}</h5>
                                    <small class="text-muted">
                                        Assignment ID: {{ $assignment->assignment_id }}
                                        @if($assignment->academicYear)
                                            | {{ $assignment->academicYear->year_name }}
                                        @endif
                                        @if($assignment->subject)
                                            | {{ $assignment->subject->name }}
                                        @endif
                                        @if($assignment->total_marks)
                                            | Total Marks: {{ number_format($assignment->total_marks, 2) }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('school.assignments.submissions.export-sample', $assignment->hashid) }}" class="btn btn-success me-2">
                                    <i class="bx bx-download me-1"></i> Download Sample Excel
                                </a>
                                <button type="button" class="btn btn-info me-2" id="importSubmissionsBtn">
                                    <i class="bx bx-upload me-1"></i> Import from Excel
                                </button>
                                <a href="{{ route('school.assignments.show', $assignment->hashid) }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Assignment Info -->
                        <div class="alert alert-info">
                            <h6 class="mb-2"><i class="bx bx-info-circle me-1"></i> Assignment Information</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Type:</strong> {{ ucwords(str_replace('_', ' ', $assignment->type)) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Due Date:</strong> {{ $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Marks:</strong> {{ $assignment->total_marks ? number_format($assignment->total_marks, 2) : 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Assigned Classes:</strong> {{ $assignment->assignmentClasses->count() }}
                                </div>
                            </div>
                        </div>

                        <!-- Import File Input (Hidden) -->
                        <input type="file" id="importFileInput" accept=".xlsx,.xls" style="display: none;">

                        @if($students->isEmpty())
                            <div class="alert alert-warning">
                                <i class="bx bx-info-circle me-1"></i> No students found for the assigned classes/streams.
                            </div>
                        @else
                            <form id="submissionsForm">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 4%;">#</th>
                                                <th style="width: 12%;">Admission No.</th>
                                                <th style="width: 18%;">Student Name</th>
                                                <th style="width: 12%;">Class/Stream</th>
                                                <th style="width: 10%;">Marks Obtained</th>
                                                <th style="width: 8%;">Percentage</th>
                                                <th style="width: 6%;">Grade</th>
                                                <th style="width: 10%;">Remark</th>
                                                <th style="width: 20%;">Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $index => $student)
                                                @php
                                                    $existingSubmission = $existingSubmissions->get($student->id);
                                                @endphp
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $student->admission_number }}</td>
                                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                    <td>
                                                        {{ $student->class ? $student->class->name : 'N/A' }}
                                                        @if($student->stream)
                                                            - {{ $student->stream->name }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               class="form-control form-control-sm marks-input" 
                                                               name="submissions[{{ $student->id }}][marks_obtained]"
                                                               value="{{ $existingSubmission ? $existingSubmission->marks_obtained : '' }}"
                                                               min="0" 
                                                               max="{{ $assignment->total_marks ?? 999999 }}"
                                                               step="0.01"
                                                               data-student-id="{{ $student->id }}"
                                                               data-total-marks="{{ $assignment->total_marks ?? 0 }}"
                                                               placeholder="0.00">
                                                        <input type="hidden" name="submissions[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                                                    </td>
                                                    <td>
                                                        <span class="percentage-display" data-student-id="{{ $student->id }}">
                                                            {{ $existingSubmission && $existingSubmission->percentage ? number_format($existingSubmission->percentage, 2) . '%' : '--' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="grade-display badge" data-student-id="{{ $student->id }}">
                                                            {{ $existingSubmission && $existingSubmission->grade ? $existingSubmission->grade : '--' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="remarks-display" data-student-id="{{ $student->id }}">
                                                            {{ $existingSubmission && $existingSubmission->remarks ? $existingSubmission->remarks : '--' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               name="submissions[{{ $student->id }}][teacher_comments]"
                                                               value="{{ $existingSubmission ? $existingSubmission->teacher_comments : '' }}"
                                                               placeholder="Enter comments...">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <small class="text-muted">
                                            <i class="bx bx-info-circle me-1"></i> 
                                            Total Students: {{ $students->count() }}
                                        </small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('school.assignments.show', $assignment->hashid) }}'">
                                            <i class="bx bx-x me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Save Submissions
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .marks-input {
        text-align: center;
    }
    .percentage-display {
        font-weight: 600;
        color: #0d6efd;
    }
    .grade-display {
        font-size: 0.9rem;
        padding: 4px 8px;
    }
    .grade-display.badge {
        background-color: #6c757d;
        color: white;
    }
    .grade-display.badge.bg-success {
        background-color: #198754 !important;
    }
    .grade-display.badge.bg-primary {
        background-color: #0d6efd !important;
    }
    .grade-display.badge.bg-info {
        background-color: #0dcaf0 !important;
    }
    .grade-display.badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    .grade-display.badge.bg-danger {
        background-color: #dc3545 !important;
    }
    .remarks-display {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Grade scale data from server
        const gradeScale = @json($gradeScaleData ?? []);

        // Calculate grade from marks
        function calculateGrade(percentage) {
            if (!percentage && percentage !== 0) return { grade: null, remarks: null };

            if (gradeScale.length > 0) {
                // Use grade scale
                for (let i = 0; i < gradeScale.length; i++) {
                    if (percentage >= gradeScale[i].min_marks && percentage <= gradeScale[i].max_marks) {
                        return {
                            grade: gradeScale[i].grade_letter,
                            remarks: gradeScale[i].remarks
                        };
                    }
                }
            }

            // Fallback to default grading
            if (percentage >= 90) return { grade: 'A', remarks: 'EXCELLENT' };
            if (percentage >= 80) return { grade: 'B', remarks: 'VERY GOOD' };
            if (percentage >= 70) return { grade: 'C', remarks: 'AVERAGE' };
            if (percentage >= 60) return { grade: 'D', remarks: 'BELOW AVERAGE' };
            return { grade: 'E', remarks: 'UNSATISFACTORY' };
        }

        // Get grade badge color
        function getGradeBadgeClass(grade) {
            if (!grade) return 'bg-secondary';
            const gradeUpper = grade.toUpperCase();
            if (gradeUpper === 'A') return 'bg-success';
            if (gradeUpper === 'B') return 'bg-primary';
            if (gradeUpper === 'C') return 'bg-info';
            if (gradeUpper === 'D') return 'bg-warning';
            if (gradeUpper === 'E' || gradeUpper === 'F') return 'bg-danger';
            return 'bg-secondary';
        }

        // Handle marks input change
        $(document).on('input', '.marks-input', function() {
            const $input = $(this);
            const studentId = $input.data('student-id');
            const totalMarks = parseFloat($input.data('total-marks')) || 0;
            let marksObtained = parseFloat($input.val()) || 0;

            // Validate: marks obtained cannot exceed total marks
            if (marksObtained > totalMarks) {
                marksObtained = totalMarks;
                $input.val(totalMarks);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Marks',
                        text: `Marks obtained cannot exceed total marks (${totalMarks})`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(`Marks obtained cannot exceed total marks (${totalMarks})`);
                }
            }

            // Calculate percentage
            let percentage = null;
            if (totalMarks > 0 && marksObtained >= 0) {
                percentage = (marksObtained / totalMarks) * 100;
            }

            // Update percentage display
            const $percentageDisplay = $(`.percentage-display[data-student-id="${studentId}"]`);
            if (percentage !== null) {
                $percentageDisplay.text(percentage.toFixed(2) + '%');
            } else {
                $percentageDisplay.text('--');
            }

            // Calculate and update grade and remarks
            const gradeResult = calculateGrade(percentage);
            const $gradeDisplay = $(`.grade-display[data-student-id="${studentId}"]`);
            const $remarksDisplay = $(`.remarks-display[data-student-id="${studentId}"]`);
            
            if (gradeResult && gradeResult.grade) {
                $gradeDisplay.text(gradeResult.grade);
                $gradeDisplay.removeClass('bg-secondary bg-success bg-primary bg-info bg-warning bg-danger');
                $gradeDisplay.addClass(getGradeBadgeClass(gradeResult.grade));
                
                // Update remarks
                if (gradeResult.remarks) {
                    $remarksDisplay.text(gradeResult.remarks);
                } else {
                    $remarksDisplay.text('--');
                }
            } else {
                $gradeDisplay.text('--');
                $gradeDisplay.removeClass('bg-success bg-primary bg-info bg-warning bg-danger');
                $gradeDisplay.addClass('bg-secondary');
                $remarksDisplay.text('--');
            }
        });

        // Handle import button click
        $('#importSubmissionsBtn').on('click', function() {
            $('#importFileInput').click();
        });

        // Handle file selection for import
        $('#importFileInput').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File',
                    text: 'Please select a valid Excel file (.xlsx or .xls)',
                    confirmButtonText: 'OK'
                });
                $(this).val('');
                return;
            }

            // Confirm import
            Swal.fire({
                title: 'Import Submissions?',
                text: 'This will update existing submissions and create new ones for students with marks in the Excel file.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0dcaf0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Import',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                    const importBtn = $('#importSubmissionsBtn');
                    const originalText = importBtn.html();
                    importBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Importing...');

                    $.ajax({
                        url: '{{ route("school.assignments.submissions.import", $assignment->hashid) }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                let message = response.message;
                                if (response.errors && response.errors.length > 0) {
                                    message += '\n\nErrors:\n' + response.errors.slice(0, 10).join('\n');
                                    if (response.errors.length > 10) {
                                        message += '\n... and ' + (response.errors.length - 10) + ' more errors';
                                    }
                                }
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Import Successful',
                                    html: message.replace(/\n/g, '<br>'),
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Import Failed',
                                    text: response.message || 'Failed to import submissions',
                                    confirmButtonText: 'OK'
                                });
                                importBtn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to import submissions';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join('<br>');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: errorMessage,
                                confirmButtonText: 'OK'
                            });
                            importBtn.prop('disabled', false).html(originalText);
                        },
                        complete: function() {
                            $('#importFileInput').val('');
                        }
                    });
                } else {
                    $(this).val('');
                }
            });
        });

        // Handle form submission
        $('#submissionsForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');

            $.ajax({
                url: '{{ route("school.assignments.submissions.store", $assignment->hashid) }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message || 'Submissions saved successfully',
                            showConfirmButton: false,
                            timer: 3000
                        }).then(() => {
                            window.location.href = '{{ route("school.assignments.show", $assignment->hashid) }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to save submissions',
                            confirmButtonText: 'OK'
                        });
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to save submissions';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: errorMessage,
                        confirmButtonText: 'OK'
                    });
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
@endpush
@endsection

