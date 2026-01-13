@extends('layouts.main')

@section('title', 'Enter Marks - ' . $examType->name . ' - ' . $schoolClass->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Marks Entry', 'url' => route('school.marks-entry.index'), 'icon' => 'bx bx-edit'],
            ['label' => 'Enter Marks', 'url' => '#', 'icon' => 'bx bx-edit-alt']
        ]" />

        <!-- Exam Info Header -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-1">
                                    <i class="bx bx-edit-alt me-2"></i>{{ $examType->name }} - {{ $schoolClass->name }}
                                </h4>
                                <p class="mb-0 opacity-75">
                                    <i class="bx bx-calendar me-1"></i>{{ $academicYear->year_name }} |
                                    <i class="bx bx-group me-1"></i>{{ $registrations->count() }} Students |
                                    <i class="bx bx-book me-1"></i>{{ $subjects->count() }} Subjects
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" class="btn btn-light me-2" onclick="history.back()">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </button>
                                <button type="button" class="btn btn-warning" id="saveBtn">
                                    <i class="bx bx-save me-1"></i>Save Marks
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            <span id="successMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div id="errorAlert" class="alert alert-danger alert-dismissible fade d-none" role="alert">
            <i class="bx bx-error-circle me-2"></i>
            <span id="errorMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Marks Entry Form -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bx bx-spreadsheet me-2 text-primary"></i>Marks Entry Sheet
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="clearBtn">
                                    <i class="bx bx-eraser me-1"></i>Clear All
                                </button>
                                <button type="button" class="btn btn-outline-info" id="loadExistingBtn">
                                    <i class="bx bx-refresh me-1"></i>Load Existing
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <form id="marksForm">
                            @csrf
                            <input type="hidden" name="exam_type_id" value="{{ $examType->id }}">
                            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">

                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="marksTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-end" style="width: 60px; min-width: 60px;">
                                                <strong>#</strong>
                                            </th>
                                            <th class="border-end" style="min-width: 200px;">
                                                <strong><i class="bx bx-user me-1"></i>Student Name</strong>
                                            </th>
                                            @foreach($subjects as $subject)
                                            <th class="text-center border-end" style="width: 100px; min-width: 100px;">
                                                <strong>{{ Str::limit($subject->name, 15) }}</strong>
                                                <br><small class="text-muted">{{ $subject->code ?? '' }}</small>
                                            </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registrations as $index => $registration)
                                        <tr>
                                            <td class="text-center fw-bold border-end">{{ $index + 1 }}</td>
                                            <td class="border-end">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <div class="avatar-initial bg-primary text-white rounded-circle">
                                                            {{ substr($registration->student->first_name, 0, 1) }}{{ substr($registration->student->last_name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $registration->student->first_name }} {{ $registration->student->last_name }}</div>
                                                        <small class="text-muted">{{ $registration->student->admission_number }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach($subjects as $subject)
                                            <td class="text-center border-end p-1">
                                                <input type="number"
                                                       class="form-control form-control-sm text-center mark-input"
                                                       name="marks[{{ $registration->student_id }}][{{ $subject->id }}]"
                                                       value="{{ $existingMarks->get($registration->student_id . '-' . $subject->id)->mark ?? '' }}"
                                                       min="0"
                                                       max="100"
                                                       step="0.01"
                                                       placeholder="-">
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <small class="text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Enter marks between 0-100. Leave blank to skip. Use Tab/Arrow keys to navigate.
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" class="btn btn-success" id="saveBtnBottom">
                                    <i class="bx bx-save me-1"></i>Save All Marks
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Saving...</span>
                </div>
                <p class="mt-2">Saving marks...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-save functionality (optional - saves after 2 seconds of inactivity)
    let autoSaveTimer;
    $('.mark-input').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Optional: implement auto-save here
        }, 2000);
    });

    // Clear all marks
    $('#clearBtn').click(function() {
        if (confirm('Are you sure you want to clear all marks? This action cannot be undone.')) {
            $('.mark-input').val('');
        }
    });

    // Load existing marks
    $('#loadExistingBtn').click(function() {
        location.reload();
    });

    // Form submission
    $('#marksForm').submit(function(e) {
        e.preventDefault();

        // Show loading modal
        $('#loadingModal').modal({
            backdrop: 'static',
            keyboard: false
        });

        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("school.marks-entry.save-marks") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#loadingModal').modal('hide');
                if (response.success) {
                    toastr.success(response.message);
                    // Optionally reload to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                let message = 'An error occurred while saving marks';

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                toastr.error(message);
            }
        });
    });

    // Save button click
    $('#saveBtn, #saveBtnBottom').click(function() {
        $('#marksForm').submit();
    });

    // Input validation
    $('.mark-input').on('input', function() {
        const value = parseFloat($(this).val());
        if (value < 0 || value > 100) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Keyboard navigation
    $('.mark-input').on('keydown', function(e) {
        const $current = $(this);
        let $next = null;

        switch(e.key) {
            case 'ArrowRight':
                $next = $current.closest('td').next().find('.mark-input');
                break;
            case 'ArrowLeft':
                $next = $current.closest('td').prev().find('.mark-input');
                break;
            case 'ArrowDown':
                const currentRow = $current.closest('tr');
                const currentIndex = currentRow.find('td').index($current.closest('td'));
                $next = currentRow.next().find('td').eq(currentIndex).find('.mark-input');
                break;
            case 'ArrowUp':
                const currentRowUp = $current.closest('tr');
                const currentIndexUp = currentRowUp.find('td').index($current.closest('td'));
                $next = currentRowUp.prev().find('td').eq(currentIndexUp).find('.mark-input');
                break;
        }

        if ($next && $next.length) {
            e.preventDefault();
            $next.focus().select();
        }
    });
});
</script>
@endsection