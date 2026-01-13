@extends('layouts.main')

@section('title', 'Import College Students')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Students', 'url' => route('college.students.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Import', 'url' => '#', 'icon' => 'bx bx-upload']
        ]" />
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">IMPORT COLLEGE STUDENTS</h6>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-arrow-back mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
        <hr />

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-upload mr-2"></i>
                                Import College Students
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Quick Stats -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                            <h4 class="mb-0">{{ $programs->count() }}</h4>
                                            <small class="text-muted">Available Programs</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-excel fa-2x text-success mb-2"></i>
                                            <h4 class="mb-0">22</h4>
                                            <small class="text-muted">Template Fields</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                                            <h4 class="mb-0">10MB</h4>
                                            <small class="text-muted">Max File Size</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                            <h4 class="mb-0">~30s</h4>
                                            <small class="text-muted">Import Time (100 records)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info-circle"></i> Import Instructions</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-list-check mr-2"></i>Steps to Import:</h6>
                                        <ol class="mb-0">
                                            <li>Select the program for your students</li>
                                            <li>Choose the academic year for enrollment</li>
                                            <li>Select admission level (First Year, Second Year, etc.)</li>
                                            <li>Download and review the Excel template</li>
                                            <li>Fill your student data in the template (Student Number, First Name, Last Name, Gender)</li>
                                            <li>Upload your completed Excel file</li>
                                            <li>Import students (all will be set to active status)</li>
                                        </ol>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-exclamation-triangle mr-2"></i>Important Notes:</h6>
                                        <ul class="mb-0">
                                            <li>Student Number must be unique</li>
                                            <li>Required fields: Student Number, First Name, Last Name, Gender</li>
                                            <li>Gender must be either 'male' or 'female'</li>
                                            <li>All students will be assigned to your current branch</li>
                                            <li>All students will be set to active status</li>
                                            <li>Current academic year is pre-selected but can be changed</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('college.students.process-import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                @csrf

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="program_id" class="form-label fw-bold">
                                                <i class="fas fa-graduation-cap mr-1"></i>
                                                Select Program <span class="text-danger">*</span>
                                            </label>
                                            <select name="program_id" id="program_id" class="form-control @error('program_id') is-invalid @enderror" required>
                                                <option value="">Choose a program...</option>
                                                @foreach($programs as $program)
                                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                        {{ $program->name }}
                                                        @if($program->department)
                                                            ({{ $program->department->name }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">
                                                <small class="text-muted">All imported students will be assigned to this program</small>
                                            </div>
                                            @error('program_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="academic_year" class="form-label fw-bold">
                                                <i class="fas fa-calendar mr-1"></i>
                                                Academic Year <span class="text-danger">*</span>
                                            </label>
                                            <select name="academic_year" id="academic_year" class="form-control @error('academic_year') is-invalid @enderror" required>
                                                <option value="">Choose academic year...</option>
                                                @foreach($academicYears as $academicYear)
                                                    <option value="{{ $academicYear->year_name }}"
                                                            {{ ($currentAcademicYear && $academicYear->id == $currentAcademicYear->id) ? 'selected' : '' }}>
                                                        {{ $academicYear->year_name }}
                                                        @if($currentAcademicYear && $academicYear->id == $currentAcademicYear->id)
                                                            <span class="badge badge-success">Current</span>
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">
                                                <small class="text-muted">Select the academic year for all imported students</small>
                                            </div>
                                            @error('academic_year')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="admission_level" class="form-label fw-bold">
                                                <i class="fas fa-level-up-alt mr-1"></i>
                                                Admission Level <span class="text-danger">*</span>
                                            </label>
                                            <select name="admission_level" id="admission_level" class="form-control @error('admission_level') is-invalid @enderror" required>
                                                <option value="">Choose admission level...</option>
                                                <option value="1" {{ old('admission_level') == '1' ? 'selected' : '' }}>First Year</option>
                                                <option value="2" {{ old('admission_level') == '2' ? 'selected' : '' }}>Second Year</option>
                                                <option value="3" {{ old('admission_level') == '3' ? 'selected' : '' }}>Third Year</option>
                                                <option value="4" {{ old('admission_level') == '4' ? 'selected' : '' }}>Fourth Year</option>
                                            </select>
                                            <div class="form-text">
                                                <small class="text-muted">Admission level for all imported students</small>
                                            </div>
                                            @error('admission_level')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="excel_file" class="form-label fw-bold">
                                                <i class="fas fa-file-excel mr-1"></i>
                                                Excel File <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                                   id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                            <div class="form-text">
                                                <small class="text-muted">Supported: .xlsx, .xls, .csv (Max: 10MB)</small>
                                            </div>
                                            @error('excel_file')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <a href="{{ route('college.students.download-template') }}"
                                               class="btn btn-outline-primary" id="downloadTemplateBtn">
                                                <i class="fas fa-download mr-2"></i> Download Template
                                            </a>

                                            <button type="submit" class="btn btn-success" id="importBtn">
                                                <i class="fas fa-upload mr-2"></i> Import Students
                                            </button>

                                            <div class="ms-auto">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    All students will be set to active status
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Progress Section -->
                            <div id="progressSection" style="display: none;" class="mt-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-spinner fa-spin mr-2"></i>
                                            Processing Import
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-3" style="height: 25px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                                 role="progressbar" style="width: 0%" id="progressBar">
                                                <span id="progressText" class="fw-bold">Initializing...</span>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <small class="text-muted" id="progressSubtext">Please wait while we process your file</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Results Section -->
                            <div id="resultsSection" style="display: none;" class="mt-4">
                                <div class="card" id="resultsCard">
                                    <div class="card-header" id="resultsHeader">
                                        <h5 class="mb-0" id="resultsTitle">
                                            <i class="fas fa-chart-bar mr-2"></i> Import Results
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert" id="resultsAlert" role="alert"></div>
                                        <div id="resultsDetails"></div>
                                    </div>
                                </div>
                            </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for program dropdown
    $('#program_id').select2({
        placeholder: 'Choose a program...',
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 for academic year dropdown
    $('#academic_year').select2({
        placeholder: 'Choose academic year...',
        allowClear: true,
        width: '100%'
    });

    // Update download template link when program changes
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        var downloadUrl = '{{ route("college.students.download-template") }}';
        if (programId) {
            downloadUrl += '?program_id=' + programId;
        }
        $('#downloadTemplateBtn').attr('href', downloadUrl);
    });

    // Set initial download URL if program is pre-selected
    var initialProgramId = $('#program_id').val();
    if (initialProgramId) {
        var downloadUrl = '{{ route("college.students.download-template") }}?program_id=' + initialProgramId;
        $('#downloadTemplateBtn').attr('href', downloadUrl);
    }

    // File validation
    $('#excel_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // MB
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];

            if (fileSize > 10) {
                alert('File size must be less than 10MB');
                this.value = '';
                return;
            }

            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
                alert('Please select a valid Excel or CSV file (.xlsx, .xls, .csv)');
                this.value = '';
                return;
            }

            // Show file info
            $('#fileInfo').remove();
            $(this).after('<small id="fileInfo" class="form-text text-success"><i class="fas fa-check-circle mr-1"></i>' + file.name + ' (' + fileSize.toFixed(2) + ' MB)</small>');
        } else {
            $('#fileInfo').remove();
        }
    });

    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        var programId = $('#program_id').val();
        var academicYear = $('#academic_year').val();
        var admissionLevel = $('#admission_level').val();

        if (!programId) {
            alert('Please select a program before importing.');
            return false;
        }

        if (!academicYear) {
            alert('Please select an academic year before importing.');
            return false;
        }

        if (!admissionLevel) {
            alert('Please select an admission level before importing.');
            return false;
        }

        var formData = new FormData(this);

        // Show progress section
        $('#progressSection').show();
        $('#progressBar').css('width', '0%');
        $('#progressText').text('Uploading file...');
        $('#progressSubtext').text('Please wait while we process your data');

        // Disable form elements
        $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Importing...');
        $('#program_id').prop('disabled', true);
        $('#academic_year').prop('disabled', true);
        $('#admission_level').prop('disabled', true);
        $('#excel_file').prop('disabled', true);

        // Hide previous results
        $('#resultsSection').hide();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = (evt.loaded / evt.total) * 50; // First 50% for upload
                        $('#progressBar').css('width', percentComplete + '%');
                        $('#progressText').text('Uploading... ' + Math.round(percentComplete) + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                $('#progressBar').css('width', '100%');
                $('#progressText').text('Import completed!');
                $('#progressSubtext').text('All records have been processed');

                setTimeout(function() {
                    $('#progressSection').hide();

                    // Show results
                    $('#resultsSection').show();
                    $('#resultsAlert').removeClass('alert-success alert-danger').addClass('alert-success');
                    $('#resultsAlert').html('<h5><i class="icon fas fa-check"></i> Import Successful!</h5>' + response.message);

                    // Re-enable form
                    $('#importBtn').prop('disabled', false).html('<i class="fas fa-upload mr-2"></i> Import Students');
                    $('#program_id').prop('disabled', false);
                    $('#academic_year').prop('disabled', false);
                    $('#admission_level').prop('disabled', false);
                    $('#excel_file').prop('disabled', false).val('').next('.custom-file-label').html('Choose file...');

                }, 1000);
            },
            error: function(xhr) {
                $('#progressSection').hide();

                // Show error results
                $('#resultsSection').show();
                $('#resultsAlert').removeClass('alert-success alert-danger').addClass('alert-danger');

                var errorMessage = 'An error occurred during import.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                $('#resultsAlert').html('<h5><i class="icon fas fa-exclamation-triangle"></i> Import Failed</h5>' + errorMessage);

                // Re-enable form
                $('#importBtn').prop('disabled', false).html('<i class="fas fa-upload mr-2"></i> Import Students');
                $('#program_id').prop('disabled', false);
                $('#academic_year').prop('disabled', false);
                $('#admission_level').prop('disabled', false);
                $('#excel_file').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush