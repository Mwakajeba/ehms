@extends('layouts.main')

@section('title', 'Import Students')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Students', 'url' => route('school.students.index'), 'icon' => 'bx bx-id-card'],
            ['label' => 'Import Students', 'url' => '#', 'icon' => 'bx bx-upload']
        ]" />
        <h6 class="mb-0 text-uppercase">IMPORT STUDENTS</h6>
        <hr />

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Import Students from Excel</h3>
                            <div class="card-tools">
                                <a href="{{ route('school.students.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-left"></i> Back to Students
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="alert alert-info">
                                        <h5><i class="icon fas fa-info"></i> Import Instructions</h5>
                                        <ul class="mb-0">
                                            <li>Select the Class and Stream for all students being imported</li>
                                            <li>Download the template file to see the required format</li>
                                            <li>Only Excel files (.xlsx, .xls) are accepted</li>
                                            <li>Maximum file size: 10MB</li>
                                            <li>Required fields in Excel: First Name (Class and Stream are auto-filled)</li>
                                            <li>Admission numbers must be unique if provided</li>
                                            <li>Dates should be in YYYY-MM-DD format</li>
                                            <li>All imported students will be assigned to the current academic year</li>
                                            <li><strong>Bus Stop names are matched flexibly</strong> - case-insensitive with partial matching</li>
                                            <li><strong>Phone Numbers:</strong> Format the Guardian Phone column as "Text" in Excel to prevent scientific notation (e.g., 2.55E+11). Use formats like +254712345678 or 0712345678.</li>
                                        </ul>
                                    </div>

                                    <form action="{{ route('school.students.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                        @csrf

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="class_id" class="form-label fw-bold">Class *</label>
                                                <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                                    <option value="">Search and select a class...</option>
                                                </select>
                                                @error('class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="stream_id" class="form-label fw-bold">Stream *</label>
                                                <select class="form-select @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id" required disabled>
                                                    <option value="">Select Class first</option>
                                                </select>
                                                @error('stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="excel_file">Select Excel File</label>
                                            <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                                   id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                                            @error('excel_file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary" id="importBtn">
                                                <i class="fas fa-upload"></i> Import Students
                                            </button>
                                            <a href="javascript:void(0);" class="btn btn-outline-secondary" id="downloadTemplateBtn" style="display: none;">
                                                <i class="fas fa-download"></i> Download Template
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Field Mapping</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Admission No</strong></td>
                                                        <td>Optional, must be unique</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>First Name *</strong></td>
                                                        <td>Required</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Last Name</strong></td>
                                                        <td>Optional</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Class</strong></td>
                                                        <td>Auto-filled from selection</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Stream</strong></td>
                                                        <td>Auto-filled from selection</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Gender</strong></td>
                                                        <td>male/female/other</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Date of Birth</strong></td>
                                                        <td>YYYY-MM-DD format (e.g., 2005-01-15)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Admission Date</strong></td>
                                                        <td>YYYY-MM-DD format (optional, defaults to today)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Address</strong></td>
                                                        <td>Optional</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Boarding Type</strong></td>
                                                        <td>day/boarding</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Has Transport</strong></td>
                                                        <td>yes/no</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Bus Stop</strong></td>
                                                        <td>Optional, flexible matching (case-insensitive)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Guardian Name</strong></td>
                                                        <td>Optional</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Guardian Phone</strong></td>
                                                        <td>Optional, format as Text in Excel</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Guardian Email</strong></td>
                                                        <td>Optional, unique</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <small class="text-muted">* Required fields</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
.table-borderless td {
    border: none;
    padding: 0.25rem 0.5rem;
}
.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
}
</style>
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for Class dropdown with live search
    $('#class_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search and select a class...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0, // Allow searching from first character
        ajax: {
            url: '{{ route("school.api.classes.search") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.classes,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        templateResult: function (classItem) {
            if (classItem.loading) return classItem.text;
            return classItem.name;
        },
        templateSelection: function (classItem) {
            return classItem.name || classItem.text;
        }
    });

    // Load streams when class is selected
    $('#class_id').change(function() {
        var classId = $(this).val();
        var streamSelect = $('#stream_id');

        if (classId) {
            // Clear current stream selection and disable dropdown
            streamSelect.prop('disabled', true).html('<option value="">Loading streams...</option>');

            // Make AJAX call to get streams for selected class
            $.ajax({
                url: '{{ route("school.api.students.streams-by-class") }}',
                type: 'GET',
                data: { class_id: classId },
                success: function(response) {
                    // Clear and populate stream dropdown
                    streamSelect.html('<option value="">Select Stream</option>');

                    if (response.streams && response.streams.length > 0) {
                        $.each(response.streams, function(index, stream) {
                            streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                        });
                    } else {
                        streamSelect.html('<option value="">No streams available for this class</option>');
                    }

                    // Re-enable dropdown
                    streamSelect.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading streams:', error);
                    streamSelect.html('<option value="">Error loading streams</option>');
                    streamSelect.prop('disabled', false);
                    alert('Error loading streams. Please try again.');
                }
            });
        } else {
            // Reset stream dropdown if no class selected
            streamSelect.html('<option value="">Select Stream</option>');
            streamSelect.prop('disabled', false);
        }

        // Hide download template button when class changes
        $('#downloadTemplateBtn').hide();
    });

    // Show/hide download template button based on class/stream selection
    $('#class_id, #stream_id').on('change', function() {
        var classId = $('#class_id').val();
        var streamId = $('#stream_id').val();

        if (classId && streamId) {
            $('#downloadTemplateBtn').show();
        } else {
            $('#downloadTemplateBtn').hide();
        }
    });
    
    // Handle download template button click
    $(document).on('click', '#downloadTemplateBtn', function(e) {
        e.preventDefault();
        
        var classId = $('#class_id').val();
        var streamId = $('#stream_id').val();
        
        if (!classId || !streamId) {
            alert('Please select both Class and Stream before downloading the template.');
            return false;
        }
        
        // Build the template URL
        var templateUrl = '{{ route("school.students.import.template") }}?class_id=' + encodeURIComponent(classId) + '&stream_id=' + encodeURIComponent(streamId);
        
        // Trigger download by navigating to the URL
        window.location.href = templateUrl;
    });

    // File validation
    $('#excel_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // MB
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];

            if (fileSize > 10) {
                alert('File size must be less than 10MB');
                this.value = '';
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid Excel file (.xlsx or .xls)');
                this.value = '';
                return;
            }
        }
    });

    // Form submission with loading state
    $('form').on('submit', function() {
        var classId = $('#class_id').val();
        var streamId = $('#stream_id').val();

        if (!classId || !streamId) {
            alert('Please select both Class and Stream before importing.');
            return false;
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');

        // Re-enable button after 30 seconds as fallback
        setTimeout(function() {
            submitBtn.prop('disabled', false).html(originalText);
        }, 30000);
    });
});
</script>
@endpush