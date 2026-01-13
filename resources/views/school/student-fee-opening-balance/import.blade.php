@extends('layouts.main')

@section('title', 'Import Student Opening Balance')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Opening Balance', 'url' => route('school.student-fee-opening-balance.index'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Import Opening Balance', 'url' => '#', 'icon' => 'bx bx-upload']
        ]" />
        <h6 class="mb-0 text-uppercase">IMPORT STUDENT OPENING BALANCE</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-upload me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">Import Student Opening Balance from Excel</h5>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-8">
                                <div class="alert alert-info">
                                    <h5><i class="bx bx-info-circle me-1"></i> Import Instructions</h5>
                                    <ul class="mb-0">
                                        <li>Select the Class and Stream for all students being imported</li>
                                        <li>Download the template file to see the required format</li>
                                        <li>Only Excel files (.xlsx, .xls) are accepted</li>
                                        <li>Maximum file size: 10MB</li>
                                        <li>Required columns: Admission Number, Amount, Fee Group, Opening Date, Notes</li>
                                        <li>Fee Group column has a dropdown list - select from available fee groups</li>
                                        <li>Opening Date should be in YYYY-MM-DD format</li>
                                        <li>All imported opening balances will be assigned to the selected academic year</li>
                                    </ul>
                                </div>

                                <form action="{{ route('school.student-fee-opening-balance.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                    @csrf

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                                <option value="">Search and select a class...</option>
                                            </select>
                                            @error('class_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="stream_id" class="form-label fw-bold">Stream</label>
                                            <select class="form-select @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id" disabled>
                                                <option value="">Select Class first</option>
                                            </select>
                                            @error('stream_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" {{ (old('academic_year_id') == $year->id) || (!old('academic_year_id') && $currentAcademicYear && $currentAcademicYear->id == $year->id) ? 'selected' : '' }}>
                                                        {{ $year->year_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('academic_year_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="excel_file" class="form-label fw-bold">Select Excel File <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                               id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                                        @error('excel_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success" id="importBtn">
                                            <i class="bx bx-upload me-1"></i> Import Opening Balance
                                        </button>
                                        <a href="#" class="btn btn-outline-primary" id="downloadTemplateBtn" style="display: none;">
                                            <i class="bx bx-download me-1"></i> Download Template
                                        </a>
                                        <a href="{{ route('school.student-fee-opening-balance.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Excel Format</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Column</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Admission Number</strong></td>
                                                    <td>Student admission number (required)</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Amount</strong></td>
                                                    <td>Opening balance amount (required)</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Fee Group</strong></td>
                                                    <td>Select from dropdown (required)</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Opening Date</strong></td>
                                                    <td>Date in YYYY-MM-DD format (required)</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Notes</strong></td>
                                                    <td>Optional notes</td>
                                                </tr>
                                            </tbody>
                                        </table>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
$(document).ready(function() {
    // Initialize Select2 for class
    $('#class_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search and select a class...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        ajax: {
            url: '{{ route("school.api.classes.search") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.classes.map(function(item) {
                        return {
                            id: item.id,
                            text: item.name,
                            name: item.name
                        };
                    }),
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
            return classItem.name || classItem.text;
        },
        templateSelection: function (classItem) {
            return classItem.name || classItem.text;
        }
    });

    // Load streams when class is selected
    $('#class_id').on('change', function() {
        var classId = $(this).val();
        var $streamSelect = $('#stream_id');
        
        if (classId) {
            $streamSelect.prop('disabled', false);
            $streamSelect.empty().append('<option value="">Loading streams...</option>');
            
            $.ajax({
                url: '{{ route("school.classes.streams", ":classId") }}'.replace(':classId', classId),
                success: function(data) {
                    $streamSelect.empty();
                    $streamSelect.append('<option value="">All Streams</option>');
                    $.each(data, function(index, stream) {
                        $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                    });
                },
                error: function() {
                    $streamSelect.empty().append('<option value="">Error loading streams</option>');
                }
            });
        } else {
            $streamSelect.prop('disabled', true).empty().append('<option value="">Select Class first</option>');
        }
        
        updateDownloadTemplateBtn();
    });

    // Update download template button
    function updateDownloadTemplateBtn() {
        var classId = $('#class_id').val();
        var streamId = $('#stream_id').val();
        var academicYearId = $('#academic_year_id').val();
        
        if (classId && academicYearId) {
            $('#downloadTemplateBtn').show();
            var url = '{{ route("school.student-fee-opening-balance.import.template") }}?class_id=' + classId + '&academic_year_id=' + academicYearId;
            if (streamId) {
                url += '&stream_id=' + streamId;
            }
            $('#downloadTemplateBtn').attr('href', url);
        } else {
            $('#downloadTemplateBtn').hide();
        }
    }

    $('#stream_id, #academic_year_id').on('change', updateDownloadTemplateBtn);

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

    // Form submission
    $('form').on('submit', function() {
        var classId = $('#class_id').val();
        var streamId = $('#stream_id').val();
        var academicYearId = $('#academic_year_id').val();

        if (!classId || !academicYearId) {
            alert('Please select Class and Academic Year before importing.');
            return false;
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Importing...');

        setTimeout(function() {
            submitBtn.prop('disabled', false).html(originalText);
        }, 30000);
    });
});
</script>
@endpush

