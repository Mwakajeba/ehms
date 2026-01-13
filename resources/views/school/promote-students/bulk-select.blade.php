@extends('layouts.main')

@section('title', 'Bulk Promote Students')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Promote Students', 'url' => route('school.promote-students.index'), 'icon' => 'bx bx-up-arrow-alt'],
            ['label' => 'Bulk Promote', 'url' => '#', 'icon' => 'bx bx-transfer']
        ]" />
        <h6 class="mb-0 text-uppercase">BULK PROMOTE STUDENTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-transfer me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">Bulk Promote Students</h5>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('errors'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Some errors occurred:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('school.promote-students.bulk-select') }}" id="filterForm">
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="bx bx-filter me-2"></i> Select Students to Promote
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h6 class="text-primary mb-3">
                                                <i class="bx bx-arrow-from-left me-2"></i> From (Current)
                                            </h6>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="from_class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                            <select class="form-select" id="from_class_id" name="from_class_id" required>
                                                <option value="">Select Class</option>
                                                @foreach($classes as $class)
                                                    @php
                                                        $classHashId = \Vinkla\Hashids\Facades\Hashids::encode($class->id);
                                                        $isSelected = request('from_class_id') == $classHashId || request('from_class_id') == $class->id;
                                                    @endphp
                                                    <option value="{{ $classHashId }}" {{ $isSelected ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="from_stream_id" class="form-label fw-bold">Stream</label>
                                            <select class="form-select" id="from_stream_id" name="from_stream_id">
                                                <option value="">All Streams</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="from_academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-select" id="from_academic_year_id" name="from_academic_year_id" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $year)
                                                    @php
                                                        $yearHashId = \Vinkla\Hashids\Facades\Hashids::encode($year->id);
                                                        $isSelected = (request('from_academic_year_id') == $yearHashId || request('from_academic_year_id') == $year->id) || (!request()->filled('from_academic_year_id') && $currentAcademicYear && $currentAcademicYear->id == $year->id);
                                                    @endphp
                                                    <option value="{{ $yearHashId }}" {{ $isSelected ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <h6 class="text-success mb-3 mt-4">
                                                <i class="bx bx-arrow-to-right me-2"></i> To (New)
                                            </h6>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="to_class_id" class="form-label fw-bold">New Class <span class="text-danger">*</span></label>
                                            <select class="form-select" id="to_class_id" name="to_class_id">
                                                <option value="">Select Class</option>
                                                @foreach($classes as $class)
                                                    @php
                                                        $classHashId = \Vinkla\Hashids\Facades\Hashids::encode($class->id);
                                                        $isSelected = request('to_class_id') == $classHashId || request('to_class_id') == $class->id;
                                                    @endphp
                                                    <option value="{{ $classHashId }}" {{ $isSelected ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="to_stream_id" class="form-label fw-bold">New Stream</label>
                                            <select class="form-select" id="to_stream_id" name="to_stream_id">
                                                <option value="">All Streams</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="to_academic_year_id" class="form-label fw-bold">New Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-select" id="to_academic_year_id" name="to_academic_year_id">
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $year)
                                                    @php
                                                        $yearHashId = \Vinkla\Hashids\Facades\Hashids::encode($year->id);
                                                        $isSelected = request('to_academic_year_id') == $yearHashId || request('to_academic_year_id') == $year->id;
                                                    @endphp
                                                    <option value="{{ $yearHashId }}" {{ $isSelected ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search me-1"></i> Load Students
                                            </button>
                                            <a href="{{ route('school.promote-students.bulk-select') }}" class="btn btn-outline-secondary">
                                                <i class="bx bx-refresh me-1"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if($students->count() > 0)
                            <!-- Selected Students List -->
                            <form method="POST" action="{{ route('school.promote-students.bulk-promote') }}" id="promoteForm">
                                @csrf
                                <input type="hidden" name="from_class_id" id="hidden_from_class_id" value="">
                                <input type="hidden" name="from_stream_id" id="hidden_from_stream_id" value="">
                                <input type="hidden" name="from_academic_year_id" id="hidden_from_academic_year_id" value="">
                                <input type="hidden" name="to_class_id" id="hidden_to_class_id" value="">
                                <input type="hidden" name="to_stream_id" id="hidden_to_stream_id" value="">
                                <input type="hidden" name="to_academic_year_id" id="hidden_to_academic_year_id" value="">

                                <div class="card border-success mb-4">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bx bx-list-ul me-2"></i> Selected Students ({{ $students->count() }})
                                        </h6>
                                        <div>
                                            <label class="form-check-label text-white me-3">
                                                <input type="checkbox" id="selectAll" class="form-check-input"> Select All
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="promotion_date" class="form-label fw-bold">Promotion Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="promotion_date" name="promotion_date" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="notes" class="form-label fw-bold">Notes</label>
                                                <input type="text" class="form-control" id="notes" name="notes" placeholder="Optional notes">
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th width="50">
                                                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                                        </th>
                                                        <th>Admission No.</th>
                                                        <th>Student Name</th>
                                                        <th>Class</th>
                                                        <th>Stream</th>
                                                        <th>Opening Balance Due</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($students as $student)
                                                    <tr class="{{ $student->already_promoted ?? false ? 'table-warning' : '' }}">
                                                        <td>
                                                            @if(!($student->already_promoted ?? false))
                                                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input student-checkbox" checked>
                                                            @else
                                                                <input type="checkbox" disabled class="form-check-input">
                                                            @endif
                                                        </td>
                                                        <td>{{ $student->admission_number }}</td>
                                                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                        <td>{{ $student->class ? $student->class->name : 'N/A' }}</td>
                                                        <td>{{ $student->stream ? $student->stream->name : 'N/A' }}</td>
                                                        <td>
                                                            @if(($student->opening_balance_due ?? 0) > 0)
                                                                <span class="text-danger fw-bold">{{ number_format($student->opening_balance_due, 2) }} {{ config('app.currency', 'TZS') }}</span>
                                                                <small class="text-muted d-block">Will be carried forward</small>
                                                            @else
                                                                <span class="text-success">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($student->already_promoted ?? false)
                                                                <span class="badge bg-warning">Already Promoted</span>
                                                            @else
                                                                <span class="badge bg-success">Ready</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(!($student->already_promoted ?? false))
                                                                <button type="button" class="btn btn-sm btn-danger remove-student" data-student-id="{{ $student->id }}">
                                                                    <i class="bx bx-x"></i> Remove
                                                                </button>
                                                            @else
                                                                <span class="text-muted">Cannot promote</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-success btn-lg" id="promoteBtn">
                                                <i class="bx bx-transfer me-1"></i> Promote Selected Students
                                            </button>
                                            <a href="{{ route('school.promote-students.index') }}" class="btn btn-secondary btn-lg">
                                                <i class="bx bx-arrow-back me-1"></i> Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @elseif(request()->has('from_class_id'))
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-1"></i> No students found matching the selected criteria.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for all select inputs
    function initializeSelect2() {
        // From Class
        $('#from_class_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select class...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0
        });

        // From Stream
        $('#from_stream_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select stream...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0
        });

        // From Academic Year
        $('#from_academic_year_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select academic year...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0
        });

        // To Class
        $('#to_class_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select class...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0
        });

        // To Stream
        $('#to_stream_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select stream...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0
        });

        // To Academic Year
        $('#to_academic_year_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select academic year...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0
        });
    }

    // Initialize Select2
    initializeSelect2();

    // Update hidden fields when Select2 values change
    $('#from_class_id, #from_stream_id, #from_academic_year_id, #to_class_id, #to_stream_id, #to_academic_year_id').on('change', function() {
        const fieldId = $(this).attr('id');
        const value = $(this).val();
        
        if (fieldId === 'from_class_id') {
            $('#hidden_from_class_id').val(value);
            loadStreamsForClass(value, '#from_stream_id', '');
        } else if (fieldId === 'from_stream_id') {
            $('#hidden_from_stream_id').val(value);
        } else if (fieldId === 'from_academic_year_id') {
            $('#hidden_from_academic_year_id').val(value);
        } else if (fieldId === 'to_class_id') {
            $('#hidden_to_class_id').val(value);
            loadStreamsForClass(value, '#to_stream_id', '');
        } else if (fieldId === 'to_stream_id') {
            $('#hidden_to_stream_id').val(value);
        } else if (fieldId === 'to_academic_year_id') {
            $('#hidden_to_academic_year_id').val(value);
        }
    });

    // Load streams for from class
    $('#from_class_id').on('change', function() {
        const selectedValue = '{{ request("from_stream_id") }}';
        loadStreamsForClass($(this).val(), '#from_stream_id', selectedValue);
    });

    // Load streams for to class
    $('#to_class_id').on('change', function() {
        const selectedValue = '{{ request("to_stream_id") }}';
        loadStreamsForClass($(this).val(), '#to_stream_id', selectedValue);
    });

    function loadStreamsForClass(classId, targetSelect, selectedValue = '') {
        const streamSelect = $(targetSelect);
        
        if (!classId) {
            streamSelect.empty().append('<option value="">All Streams</option>');
            streamSelect.trigger('change');
            return;
        }

        streamSelect.prop('disabled', true);
        streamSelect.html('<option value="">Loading...</option>').trigger('change');

        $.ajax({
            url: '{{ route("school.api.students.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            success: function(response) {
                streamSelect.empty();
                streamSelect.append('<option value="">All Streams</option>');

                if (response.streams && response.streams.length > 0) {
                    response.streams.forEach(function(stream) {
                        // Stream ID is already encoded from server (hash ID)
                        const isSelected = selectedValue == stream.id || 
                                         (selectedValue && selectedValue.toString() === stream.id.toString());
                        streamSelect.append(`<option value="${stream.id}" ${isSelected ? 'selected' : ''}>${stream.name}</option>`);
                    });
                }
                streamSelect.prop('disabled', false);
                streamSelect.trigger('change');
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.empty().append('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', false);
                streamSelect.trigger('change');
            }
        });
    }

    // Initialize hidden fields with current values on page load
    function updateHiddenFields() {
        $('#hidden_from_class_id').val($('#from_class_id').val());
        $('#hidden_from_stream_id').val($('#from_stream_id').val());
        $('#hidden_from_academic_year_id').val($('#from_academic_year_id').val());
        $('#hidden_to_class_id').val($('#to_class_id').val());
        $('#hidden_to_stream_id').val($('#to_stream_id').val());
        $('#hidden_to_academic_year_id').val($('#to_academic_year_id').val());
    }

    // Update hidden fields initially
    setTimeout(function() {
        updateHiddenFields();
    }, 100);

    // Update hidden fields before form submission
    $('#filterForm').on('submit', function(e) {
        updateHiddenFields();
        // Form will submit normally
    });

    // Load streams on page load if class is selected
    @if(request('from_class_id'))
        @php
            $fromClassId = request('from_class_id');
            try {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($fromClassId);
                $fromClassId = $decoded[0] ?? $fromClassId;
            } catch (\Exception $e) {
                // Keep original value if not a hash
            }
        @endphp
        setTimeout(function() {
            loadStreamsForClass('{{ $fromClassId }}', '#from_stream_id', '{{ request("from_stream_id") }}');
        }, 500);
    @endif

    @if(request('to_class_id'))
        @php
            $toClassId = request('to_class_id');
            try {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($toClassId);
                $toClassId = $decoded[0] ?? $toClassId;
            } catch (\Exception $e) {
                // Keep original value if not a hash
            }
        @endphp
        setTimeout(function() {
            loadStreamsForClass('{{ $toClassId }}', '#to_stream_id', '{{ request("to_stream_id") }}');
        }, 500);
    @endif

    // Select all checkbox
    $('#selectAll, #selectAllCheckbox').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.student-checkbox:not(:disabled)').prop('checked', isChecked);
        $('#selectAll, #selectAllCheckbox').prop('checked', isChecked);
    });

    // Remove student button
    $('.remove-student').on('click', function() {
        const studentId = $(this).data('student-id');
        $(this).closest('tr').find('.student-checkbox').prop('checked', false);
        $(this).closest('tr').fadeOut(300, function() {
            $(this).remove();
        });
    });

    // Form submission
    $('#promoteForm').on('submit', function(e) {
        const checkedCount = $('.student-checkbox:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Please select at least one student to promote.');
            return false;
        }

        const submitBtn = $('#promoteBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Promoting...');
    });
});
</script>
@endpush

