@extends('layouts.main')

@section('title', 'Enter Examination Marks')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Enter Marks', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-edit me-1 font-22 text-primary"></i>
                                <span class="h5 mb-0 text-primary">Enter Examination Marks</span>
                            </div>
                            <div>
                                <a href="{{ route('school.academics-examinations.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Selection Form -->
                        <div id="selectionForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="examTypeSelect" class="form-label fw-bold">
                                        <i class="bx bx-category me-1 text-primary"></i>Exam Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="examTypeSelect" required>
                                        <option value="">Select Exam Type</option>
                                        @foreach($examTypes ?? [] as $examType)
                                            <option value="{{ $examType->id }}">{{ $examType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="classSelect" class="form-label fw-bold">
                                        <i class="bx bx-group me-1 text-primary"></i>Class <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="classSelect" disabled required>
                                        <option value="">Select Class</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="streamSelect" class="form-label fw-bold">
                                        <i class="bx bx-branch me-1 text-primary"></i>Stream <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="streamSelect" disabled required>
                                        <option value="">Select Stream</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 text-center">
                                    <button type="button" class="btn btn-primary btn-lg" id="loadMarksBtn" disabled>
                                        <i class="bx bx-search me-2"></i> Load Marks Entry
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Marks Entry Table -->
                        <div id="marksTableContainer" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-0 text-primary">
                                        <i class="bx bx-list-ul me-2"></i>Student Marks Entry
                                        <small class="text-muted ms-2" id="selectedInfo"></small>
                                    </h5>
                                    <small class="text-muted">Enter marks for each student and subject</small>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-success btn-lg me-2" id="saveMarksBtn">
                                        <i class="bx bx-save me-2"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-info btn-lg" id="importMarksBtn" data-bs-toggle="modal" data-bs-target="#importMarksModal">
                                        <i class="bx bx-upload me-2"></i> Import Marks
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Instructions:</strong> Enter marks between 0-100. Leave blank for absent students. Click "Save Changes" when done.
                            </div>

                            <div class="table-responsive border rounded">
                                <table class="table table-striped table-hover mb-0" id="marksTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center fw-bold">#</th>
                                            <th class="fw-bold">Student Name</th>
                                            <th class="text-center fw-bold">Admission No.</th>
                                            <!-- Subject columns will be added dynamically -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Student rows will be added dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 text-center">
                                <button type="button" class="btn btn-success btn-lg me-2" id="saveMarksBtnBottom">
                                    <i class="bx bx-save me-2"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="backToSelectionBtn">
                                    <i class="bx bx-arrow-back me-1"></i> Change Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Marks Modal -->
        <div class="modal fade" id="importMarksModal" tabindex="-1" aria-labelledby="importMarksModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importMarksModalLabel">
                            <i class="bx bx-upload me-2"></i>Import Marks
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>How to Import Marks:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Download the sample Excel template below</li>
                                <li>Fill in the marks for each student and subject</li>
                                <li>Upload the completed Excel file</li>
                                <li>Review and confirm the import</li>
                            </ol>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold">Step 1: Download Sample Template</h6>
                                <p class="text-muted small">Download a sample Excel file with the current class/stream and subjects setup.</p>
                                <button type="button" class="btn btn-primary" id="downloadSampleBtn">
                                    <i class="bx bx-download me-2"></i> Download Sample Excel
                                </button>
                            </div>

                            <div class="col-12">
                                <hr>
                                <h6 class="fw-bold">Step 2: Upload Completed File</h6>
                                <p class="text-muted small">Upload your completed Excel file with marks filled in.</p>
                                <input type="file" class="form-control" id="marksFileInput" accept=".xlsx,.xls" style="display: none;">
                                <button type="button" class="btn btn-outline-primary" id="uploadFileBtn">
                                    <i class="bx bx-file me-2"></i> Choose Excel File
                                </button>
                                <span id="selectedFileName" class="ms-2 text-muted"></span>
                            </div>

                            <div class="col-12">
                                <hr>
                                <h6 class="fw-bold">Step 3: Import Marks</h6>
                                <button type="button" class="btn btn-success" id="importMarksConfirmBtn" disabled>
                                    <i class="bx bx-check me-2"></i> Import Marks
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
        border-radius: 0.375rem;
    }

    .mark-input {
        width: 80px;
        text-align: center;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .mark-input:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .mark-input.changed {
        border-color: #ffc107;
        background-color: #fff3cd;
    }

    .mark-input.is-valid.changed {
        border-color: #28a745;
        background-color: #d4edda;
    }

    .table th {
        position: sticky;
        top: 0;
        background-color: #343a40 !important;
        border-color: #454d55 !important;
        z-index: 10;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.75rem;
        vertical-align: middle;
    }

    .table td {
        vertical-align: middle;
        padding: 0.5rem;
    }

    .table-dark th {
        background-color: #343a40 !important;
        border-color: #454d55 !important;
    }

    .card-title {
        margin-bottom: 0;
    }

    .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-lg {
        padding: 0.5rem 1.5rem;
        font-size: 1rem;
    }

    .alert {
        border-radius: 0.375rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
    }

    /* Select2 Bootstrap 5 Theme Styling */
    .select2-container {
        width: 100% !important;
    }
    
    /* Ensure Select2 matches Bootstrap form-select styling */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        padding-right: 20px;
    }
    
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .select2-dropdown--bootstrap-5 {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .select2-container--bootstrap-5 .select2-results__option {
        padding: 0.5rem 0.75rem;
    }
    
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #0d6efd;
        color: white;
    }
    
    .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
        background-color: #6c757d;
        color: white;
    }
    
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        margin: 0.5rem;
        width: calc(100% - 1rem);
    }
    
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        outline: 0;
    }
</style>
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    let currentAcademicYearId = null;

    // Initialize Select2 for all selects
    initializeSelect2();

    // When exam type is selected
    $('#examTypeSelect').on('change', function() {
        const examTypeId = $(this).val();
        if (examTypeId) {
            loadClassesForMarksEntry(examTypeId);
        } else {
            $('#classSelect').prop('disabled', true).html('<option value="">Select Class</option>');
            $('#streamSelect').prop('disabled', true).html('<option value="">Select Stream</option>');
            $('#loadMarksBtn').prop('disabled', true);
        }
    });

    // When class is selected
    $('#classSelect').on('change', function() {
        const classId = $(this).val();
        const examTypeId = $('#examTypeSelect').val();

        if (classId && examTypeId) {
            // For specific class, load streams
            loadStreamsForClass(classId, examTypeId);
            $('#loadMarksBtn').prop('disabled', true); // Keep disabled until stream is selected
        } else {
            $('#streamSelect').prop('disabled', true).html('<option value="">Select Stream</option>');
            $('#loadMarksBtn').prop('disabled', true);
        }
    });

    // When stream is selected (now required)
    $('#streamSelect').on('change', function() {
        const streamId = $(this).val();
        const classId = $('#classSelect').val();
        const examTypeId = $('#examTypeSelect').val();

        if (classId && examTypeId && streamId) {
            $('#loadMarksBtn').prop('disabled', false);
        } else {
            $('#loadMarksBtn').prop('disabled', true);
        }
    });

    // Load marks button click
    $('#loadMarksBtn').on('click', function() {
        loadMarksEntryData();
    });

    // Function to initialize Select2
    function initializeSelect2() {
        // Only initialize Select2 if it's available
        if (typeof $.fn.select2 !== 'undefined') {
            // Initialize exam type select with live search
            $('#examTypeSelect').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
            width: '100%',
                        placeholder: function() {
                            return $(this).find('option:first').text();
                        },
                        allowClear: true
                    });
                }
        });

            // Initialize class select with live search
            $('#classSelect').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
            width: '100%',
                        placeholder: function() {
                            return $(this).find('option:first').text();
                        },
                        allowClear: true
                    });
                }
        });

            // Initialize stream select with live search
            $('#streamSelect').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
            width: '100%',
                        placeholder: function() {
                            return $(this).find('option:first').text();
                        },
                        allowClear: true
        });
                }
            });
        }
    }        // Load classes for marks entry
        function loadClassesForMarksEntry(examTypeId) {
            $.ajax({
                url: '{{ route("school.academics-examinations.get-classes-for-marks-entry") }}',
                method: 'POST',
                data: {
                    exam_type_id: examTypeId,
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    $('#classSelect').prop('disabled', true).html('<option value="">Loading...</option>');
                },
                success: function(response) {
                    currentAcademicYearId = response.academic_year_id;
                    let options = '<option value="">Select Class</option>';
                    response.classes.forEach(function(cls) {
                        options += `<option value="${cls.id}" data-streams='${JSON.stringify(cls.streams)}'>${cls.name}</option>`;
                    });
                    $('#classSelect').html(options).prop('disabled', false);
                    // Reinitialize Select2 for class select with live search
                    if (typeof $.fn.select2 !== 'undefined') {
                        $('#classSelect').select2('destroy');
                    $('#classSelect').select2({
                            theme: 'bootstrap-5',
                        width: '100%',
                            placeholder: function() {
                                return $(this).find('option:first').text();
                            },
                            allowClear: true
                    });
                    }
                    $('#streamSelect').prop('disabled', true).html('<option value="">Select Stream</option>');
                    $('#loadMarksBtn').prop('disabled', true);
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to load classes', 'error');
                    $('#classSelect').prop('disabled', true).html('<option value="">Select Class</option>');
                    console.error(xhr);
                }
            });
        }

        // Load streams for selected class
        function loadStreamsForClass(classId, examTypeId) {
            const selectedOption = $('#classSelect option:selected');
            const streamsData = selectedOption.data('streams');

            if (streamsData && streamsData.length > 0) {
                let options = '<option value="">Select Stream</option>';
                streamsData.forEach(function(stream) {
                    options += `<option value="${stream.id}">${stream.name}</option>`;
                });
                $('#streamSelect').html(options).prop('disabled', false);
                // Reinitialize Select2 for stream select with live search
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#streamSelect').select2('destroy');
                $('#streamSelect').select2({
                        theme: 'bootstrap-5',
                    width: '100%',
                        placeholder: function() {
                            return $(this).find('option:first').text();
                        },
                        allowClear: true
                });
                }
            } else {
                $('#streamSelect').html('<option value="">No streams available</option>').prop('disabled', true);
                $('#loadMarksBtn').prop('disabled', true);
            }
        }

        // Load marks entry data
        function loadMarksEntryData() {
            const examTypeId = $('#examTypeSelect').val();
            const classId = $('#classSelect').val();
            const streamId = $('#streamSelect').val();

            if (!examTypeId) {
                Swal.fire('Error', 'Please select an exam type.', 'error');
                return;
            }
            if (!classId) {
                Swal.fire('Error', 'Please select a class.', 'error');
                return;
            }
            if (classId !== 'all' && !streamId) {
                Swal.fire('Error', 'Please select a stream.', 'error');
                return;
            }
            if (!currentAcademicYearId) {
                Swal.fire('Error', 'Academic year not loaded. Please select exam type again.', 'error');
                return;
            }

            // For specific class and stream
            const ajaxData = {
                exam_type_id: examTypeId,
                class_id: classId,
                academic_year_id: currentAcademicYearId,
                stream_id: streamId
            };

            $.ajax({
                url: '{{ route("school.academics-examinations.get-marks-entry-data") }}',
                method: 'GET',
                data: ajaxData,
                beforeSend: function() {
                    $('#marksTableContainer').hide();
                    Swal.fire({
                        title: 'Loading...',
                        text: 'Loading marks entry data...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    console.log('AJAX Success - Raw response:', response);
                    Swal.close();
                    if (response.error) {
                        console.error('Response contains error:', response.error);
                        Swal.fire('Error', response.error, 'error');
                        return;
                    }

                    try {
                        console.log('Processing response data...');

                        // Extract data from response
                        const students = response.students || [];
                        const subjects = response.subjects || [];
                        const existingMarks = response.existing_marks || [];
                        const registrations = response.registrations || [];

                        console.log('Data extracted:', {
                            students: students.length,
                            subjects: subjects.length,
                            existingMarks: existingMarks.length,
                            registrations: registrations.length
                        });

                        // Validate data
                        if (!Array.isArray(students) || !Array.isArray(subjects)) {
                            throw new Error('Invalid data format: students and subjects must be arrays');
                        }

                        if (students.length === 0) {
                            // Check if a specific stream was selected
                            const streamId = $('#streamSelect').val();
                            const streamText = $('#streamSelect option:selected').text();

                            if (streamId && streamText !== 'Select Stream') {
                                throw new Error(`This stream (${streamText}) has no students`);
                            } else {
                                throw new Error('No students found for the selected criteria');
                            }
                        }

                        if (subjects.length === 0) {
                            throw new Error('No subjects found for the selected criteria');
                        }

                        // Process existing marks into object format
                        const existingMarksObj = {};
                        existingMarks.forEach(function(mark) {
                            existingMarksObj[mark.student_id + '-' + mark.subject_id] = mark;
                        });

                        // Process registrations into object format
                        const registrationsObj = {};
                        registrations.forEach(function(reg) {
                            registrationsObj[reg.student_id + '-' + reg.subject_id] = reg;
                        });

                        console.log('Data validation passed, building table...');

                        // Build the marks entry table
                        buildMarksTable(students, subjects, existingMarksObj, registrationsObj);

                        console.log('Table built successfully');

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Marks Entry Loaded',
                            text: `Loaded ${students.length} students and ${subjects.length} subjects`,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Hide selection form and show table
                        $('#selectionForm').hide();

                    } catch (error) {
                        console.error('Error processing marks entry data:', error);
                        $('#loadingSpinner').hide();
                        $('#loadMarksBtn').prop('disabled', false).html('<i class="bx bx-refresh me-2"></i>Reload Marks Entry');

                        Swal.fire({
                            icon: 'error',
                            title: 'Error Loading Marks Entry',
                            text: error.message || 'An error occurred while loading marks entry data',
                            footer: 'Check the browser console for more details'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    const response = xhr.responseJSON;
                    if (response && response.error) {
                        Swal.fire('Error', response.error, 'error');
                    } else {
                        Swal.fire('Error', 'Failed to load marks entry data', 'error');
                    }
                    console.error(xhr);
                }
            });
        }

        // Change selection button
        $('#backToSelectionBtn').on('click', function() {
            $('#marksTableContainer').hide();
            $('#selectionForm').show();
            // Reset Select2 selects
            $('#examTypeSelect').val('').trigger('change');
            $('#classSelect').val('').trigger('change');
            $('#streamSelect').val('').trigger('change');
            $('#loadMarksBtn').prop('disabled', true);
        });

        // Build marks entry table
        function buildMarksTable(students, subjects, existingMarks, registrations) {
            console.log('buildMarksTable called with:', {
                students: students.length,
                subjects: subjects.length,
                existingMarks: Object.keys(existingMarks).length,
                registrations: Object.keys(registrations).length
            });

            try {
                // Check if we have multiple different classes
                const classIds = [...new Set(students.map(student => student.class_id))];
                const hasMultipleClasses = classIds.length > 1;
                const groupedByClass = hasMultipleClasses;

                console.log('Table configuration:', {
                    classIds: classIds,
                    hasMultipleClasses: hasMultipleClasses,
                    groupedByClass: groupedByClass
                });

                // Build table header
                let headerHtml = '<tr><th class="text-center">#</th>';
                if (groupedByClass) {
                    headerHtml += '<th class="text-start">Class</th>';
                }
                headerHtml += '<th class="text-start">Student Name</th>';

                subjects.forEach(function(subject) {
                    headerHtml += `<th class="text-center" style="min-width: 100px; font-size: 0.8rem;">${subject.short_name || subject.name}</th>`;
                });
                headerHtml += '</tr>';

                console.log('Header HTML length:', headerHtml.length);

                // Build table body
                let bodyHtml = '';
                let counter = 1;

                if (groupedByClass) {
                    console.log('Building grouped table');
                    // Group students by class
                    const studentsByClass = {};
                    students.forEach(function(student) {
                        const classKey = student.class_id;
                        if (!studentsByClass[classKey]) {
                            studentsByClass[classKey] = {
                                class_name: student.class_name,
                                students: []
                            };
                        }
                        studentsByClass[classKey].students.push(student);
                    });

                    // Build table with class groupings
                    Object.keys(studentsByClass).forEach(function(classId) {
                        const classData = studentsByClass[classId];

                        // Class header row
                        bodyHtml += `<tr class="table-secondary">
                            <td colspan="${3 + subjects.length}" class="fw-bold text-primary">
                                <i class="bx bx-group me-2"></i>${classData.class_name}
                            </td>
                        </tr>`;

                        // Student rows for this class
                        classData.students.forEach(function(student) {
                            bodyHtml += `<tr>
                                <td class="text-center fw-bold">${counter++}</td>
                                <td class="fw-bold">${student.class_name}</td>
                                <td class="fw-bold">${student.name}</td>`;

                            subjects.forEach(function(subject) {
                                const markKey = `${student.id}-${subject.id}`;
                                const existingMark = existingMarks[markKey];
                                const registration = registrations[markKey];
                                const registrationStatus = registration ? registration.status : null;
                                const isRegistered = registrationStatus === 'registered';
                                
                                // Only show marks input if student is registered
                                if (isRegistered) {
                                const markValue = existingMark ? existingMark.mark : '';
                                const isValid = markValue !== '' && !isNaN(markValue) && markValue >= 0 && markValue <= 100;
                                bodyHtml += `<td class="text-center">
                                        <input type="number" class="form-control form-control-sm mark-input ${isValid ? 'is-valid' : ''}"
                                           data-student-id="${student.id}"
                                           data-subject-id="${subject.id}"
                                           value="${markValue}"
                                           min="0" max="100" step="0.01"
                                               placeholder="-">
                                </td>`;
                                } else {
                                    // Show status instead of marks input
                                    let statusText = 'Not Registered';
                                    let statusClass = 'bg-secondary';
                                    if (registrationStatus === 'absent') {
                                        statusText = 'Absent';
                                        statusClass = 'bg-danger';
                                    } else if (registrationStatus === 'exempted') {
                                        statusText = 'Exempted';
                                        statusClass = 'bg-warning';
                                    } else if (registrationStatus === 'attended') {
                                        statusText = 'Attended';
                                        statusClass = 'bg-info';
                                    }
                                    bodyHtml += `<td class="text-center">
                                        <span class="badge ${statusClass} text-white px-2 py-1">${statusText}</span>
                                    </td>`;
                                }
                            });
                            bodyHtml += '</tr>';
                        });
                    });
                } else {
                    console.log('Building single class table');
                    // Single class - original format
                    students.forEach(function(student, index) {
                        bodyHtml += `<tr>
                            <td class="text-center fw-bold">${counter++}</td>
                            <td class="fw-bold">${student.name}</td>`;

                        subjects.forEach(function(subject) {
                            const markKey = `${student.id}-${subject.id}`;
                            const existingMark = existingMarks[markKey];
                            const registration = registrations[markKey];
                            const registrationStatus = registration ? registration.status : null;
                            const isRegistered = registrationStatus === 'registered';
                            
                            // Only show marks input if student is registered
                            if (isRegistered) {
                            const markValue = existingMark ? existingMark.mark : '';
                            const isValid = markValue !== '' && !isNaN(markValue) && markValue >= 0 && markValue <= 100;
                            bodyHtml += `<td class="text-center">
                                    <input type="number" class="form-control form-control-sm mark-input ${isValid ? 'is-valid' : ''}"
                                       data-student-id="${student.id}"
                                       data-subject-id="${subject.id}"
                                       data-original-value="${markValue}"
                                       value="${markValue}"
                                       min="0" max="100" step="0.01"
                                           placeholder="-">
                            </td>`;
                            } else {
                                // Show status instead of marks input
                                let statusText = 'Not Registered';
                                let statusClass = 'bg-secondary';
                                if (registrationStatus === 'absent') {
                                    statusText = 'Absent';
                                    statusClass = 'bg-danger';
                                } else if (registrationStatus === 'exempted') {
                                    statusText = 'Exempted';
                                    statusClass = 'bg-warning';
                                } else if (registrationStatus === 'attended') {
                                    statusText = 'Attended';
                                    statusClass = 'bg-info';
                                }
                                bodyHtml += `<td class="text-center">
                                    <span class="badge ${statusClass} text-white px-2 py-1">${statusText}</span>
                                </td>`;
                            }
                        });
                        bodyHtml += '</tr>';
                    });
                }

                console.log('Body HTML length:', bodyHtml.length);
                console.log('Setting table HTML...');

                $('#marksTable thead').html(headerHtml);
                $('#marksTable tbody').html(bodyHtml);

                console.log('Table HTML set, updating selected info...');
                // Update selected info
                updateSelectedInfo();

                console.log('buildMarksTable completed successfully');

                // Show the table and hide loading
                $('#marksTableContainer').show();
                $('#loadingSpinner').hide();
                $('#loadMarksBtn').prop('disabled', false).html('<i class="bx bx-refresh me-2"></i>Reload Marks Entry');

                console.log('Table visibility updated');
            } catch (error) {
                console.error('Error in buildMarksTable:', error);
                throw error;
            }
        }

        // Update selected class and stream info
        function updateSelectedInfo() {
            const examTypeText = $('#examTypeSelect option:selected').text();
            const classText = $('#classSelect option:selected').text();
            const streamText = $('#streamSelect option:selected').text();

            let info = `(${examTypeText}`;
            if (classText && classText !== 'Select Class') {
                info += ` - ${classText}`;
            }
            if (streamText && streamText !== 'Select Stream') {
                info += ` - ${streamText}`;
            }
            info += ')';

            $('#selectedInfo').text(info);
        }

        // Validate marks input
        $(document).on('input', '.mark-input', function() {
            const value = $(this).val();
            const originalValue = $(this).data('original-value') || '';
            const isValid = value === '' || (!isNaN(value) && value >= 0 && value <= 100);

            if (isValid) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }

            // Mark as changed if different from original
            if (value !== originalValue) {
                $(this).addClass('changed');
            } else {
                $(this).removeClass('changed');
            }
        });

        // Navigate to next input field on Enter key press
        $(document).on('keydown', '.mark-input', function(e) {
            if (e.keyCode === 13) { // Enter key
                e.preventDefault(); // Prevent form submission

                const currentInput = $(this);
                const currentSubjectId = currentInput.data('subject-id');
                const currentRow = currentInput.closest('tr');

                // Find the next row (next student)
                const nextRow = currentRow.next('tr:not(.table-secondary)'); // Skip class header rows

                if (nextRow.length > 0) {
                    // Find the input field in the next row with the same subject
                    const nextInput = nextRow.find(`input.mark-input[data-subject-id="${currentSubjectId}"]`);

                    if (nextInput.length > 0 && !nextInput.prop('disabled')) {
                        nextInput.focus();
                        // Optionally select all text in the input
                        nextInput.select();
                    }
                }
            }
        });

        // Save marks (both buttons)
        $('#saveMarksBtn, #saveMarksBtnBottom').on('click', function() {
            const examTypeId = $('#examTypeSelect').val();
            const marks = [];
            let hasInvalidMarks = false;

            $('.mark-input:not(:disabled)').each(function() {
                const studentId = $(this).data('student-id');
                const subjectId = $(this).data('subject-id');
                const mark = $(this).val().trim();
                const originalValue = $(this).data('original-value') || '';

                // Only include marks that have changed
                if (mark !== originalValue) {
                    if (mark !== '') {
                        const markValue = parseFloat(mark);
                        if (isNaN(markValue) || markValue < 0 || markValue > 100) {
                            hasInvalidMarks = true;
                            $(this).addClass('is-invalid');
                            return false; // Break out of each loop
                        }
                    }
                    marks.push({
                        student_id: studentId,
                        subject_id: subjectId,
                        mark: mark === '' ? null : parseFloat(mark)
                    });
                }
            });

            if (hasInvalidMarks) {
                toastr.error('Please correct invalid marks (must be between 0-100)');
                return;
            }

            if (marks.length === 0) {
                toastr.warning('No marks have been changed');
                return;
            }

            // Confirm save
            Swal.fire({
                title: 'Confirm Save',
                text: `Are you sure you want to save ${marks.length} changed marks?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save Changes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    saveMarksAjax(examTypeId, marks);
                }
            });
        });

        // Save marks function
        function saveMarks(examTypeId, marks) {
            // Confirm save
            Swal.fire({
                title: 'Confirm Save',
                text: `Are you sure you want to save ${marks.length} marks?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    saveMarksAjax(examTypeId, marks);
                }
            });
        }

        // Save marks AJAX function
        function saveMarksAjax(examTypeId, marks) {
            const classId = $('#classSelect').val();
            const streamId = $('#streamSelect').val();

            const ajaxData = {
                exam_type_id: examTypeId,
                academic_year_id: currentAcademicYearId,
                class_id: classId,
                marks: marks,
                _token: '{{ csrf_token() }}'
            };

            if (streamId) {
                ajaxData.stream_id = streamId;
            }

            $.ajax({
                url: '{{ route("school.academics-examinations.save-marks") }}',
                method: 'POST',
                data: ajaxData,
                beforeSend: function() {
                    $('#saveMarksBtn, #saveMarksBtnBottom').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i> Saving...');
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Saving marks...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire('Success', response.message || 'Marks saved successfully', 'success');
                    $('#saveMarksBtn, #saveMarksBtnBottom').prop('disabled', false).html('<i class="bx bx-save me-2"></i> Save Changes');

                    // Update original values and remove changed class for saved inputs
                    $('.mark-input:not(:disabled)').each(function() {
                        const value = $(this).val().trim();
                        $(this).data('original-value', value);
                        $(this).removeClass('changed');
                        if (value !== '') {
                            $(this).addClass('is-valid').removeClass('is-invalid');
                        }
                    });
                },
                error: function(xhr) {
                    Swal.close();
                    $('#saveMarksBtn, #saveMarksBtnBottom').prop('disabled', false).html('<i class="bx bx-save me-2"></i> Save Changes');
                    const response = xhr.responseJSON;
                    if (response && response.errors) {
                        // Display validation errors
                        let errorMessages = '';
                        Object.keys(response.errors).forEach(function(key) {
                            response.errors[key].forEach(function(error) {
                                errorMessages += error + '\n';
                            });
                        });
                        Swal.fire('Error', errorMessages, 'error');
                    } else {
                        Swal.fire('Error', response ? response.message : 'Failed to save marks', 'error');
                    }
                    console.error(xhr);
                }
            });
        }

        // Import Marks Modal functionality
        $('#downloadSampleBtn').on('click', function() {
            const examTypeId = $('#examTypeSelect').val();
            const classId = $('#classSelect').val();
            const streamId = $('#streamSelect').val();

            if (!examTypeId) {
                Swal.fire('Error', 'Please select an exam type first.', 'error');
                return;
            }

            // Build download URL with current selections
            let downloadUrl = '{{ route("school.academics-examinations.download-marks-sample") }}?exam_type_id=' + examTypeId;

            if (classId) {
                downloadUrl += '&class_id=' + classId;
            }

            if (streamId) {
                downloadUrl += '&stream_id=' + streamId;
            }

            if (currentAcademicYearId) {
                downloadUrl += '&academic_year_id=' + currentAcademicYearId;
            }

            // Trigger download by navigating to the URL
            window.location.href = downloadUrl;

            Swal.fire({
                icon: 'success',
                title: 'Download Started',
                text: 'Your sample Excel file is being downloaded.',
                timer: 2000,
                showConfirmButton: false
            });
        });

        // File upload functionality
        $('#uploadFileBtn').on('click', function() {
            $('#marksFileInput').click();
        });

        $('#marksFileInput').on('change', function() {
            const file = this.files[0];
            if (file) {
                $('#selectedFileName').text(file.name);
                $('#importMarksConfirmBtn').prop('disabled', false);
            } else {
                $('#selectedFileName').text('');
                $('#importMarksConfirmBtn').prop('disabled', true);
            }
        });

        // Import marks confirmation
        $('#importMarksConfirmBtn').on('click', function() {
            const file = $('#marksFileInput')[0].files[0];
            if (!file) {
                Swal.fire('Error', 'Please select a file to import.', 'error');
                return;
            }

            const examTypeId = $('#examTypeSelect').val();
            const classId = $('#classSelect').val();
            const streamId = $('#streamSelect').val();

            if (!examTypeId) {
                Swal.fire('Error', 'Please select an exam type.', 'error');
                return;
            }

            if (!currentAcademicYearId) {
                Swal.fire('Error', 'Academic year not loaded. Please reload the page.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Import',
                text: 'Are you sure you want to import marks from the selected Excel file? This will overwrite any existing marks for the students and subjects in the file.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Import',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    importMarksAjax(file, examTypeId, classId, streamId);
                }
            });
        });

        // Import marks AJAX function
        function importMarksAjax(file, examTypeId, classId, streamId) {
            const formData = new FormData();
            formData.append('marks_file', file);
            formData.append('exam_type_id', examTypeId);
            formData.append('class_id', classId);
            formData.append('academic_year_id', currentAcademicYearId);
            formData.append('_token', '{{ csrf_token() }}');

            if (streamId) {
                formData.append('stream_id', streamId);
            }

            $.ajax({
                url: '{{ route("school.academics-examinations.import-marks") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#importMarksConfirmBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i> Importing...');
                    Swal.fire({
                        title: 'Importing...',
                        text: 'Processing marks import...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    $('#importMarksConfirmBtn').prop('disabled', false).html('<i class="bx bx-check me-2"></i> Import Marks');
                    $('#importMarksModal').modal('hide');
                    $('#marksFileInput').val('');
                    $('#selectedFileName').text('');

                    if (response.success) {
                        let message = response.message;
                        if (response.errors && response.errors.length > 0) {
                            message += '\n\nErrors:\n' + response.errors.join('\n');
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Import Completed',
                            text: message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload the marks entry data to show imported marks
                            loadMarksEntryData();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Import failed', 'error');
                    }
                },
                error: function(xhr) {
                    $('#importMarksConfirmBtn').prop('disabled', false).html('<i class="bx bx-check me-2"></i> Import Marks');
                    const response = xhr.responseJSON;
                    if (response && response.errors) {
                        let errorMessages = 'Import failed:\n';
                        Object.keys(response.errors).forEach(function(key) {
                            response.errors[key].forEach(function(error) {
                                errorMessages += error + '\n';
                            });
                        });
                        Swal.fire('Error', errorMessages, 'error');
                    } else {
                        Swal.fire('Error', response ? response.message : 'Import failed', 'error');
                    }
                    console.error(xhr);
                }
            });
        }
    });
</script>
@endpush