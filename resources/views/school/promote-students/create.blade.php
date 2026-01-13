@extends('layouts.main')

@section('title', 'Promote Students - Select Students')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Promote Students', 'url' => route('school.promote-students.index'), 'icon' => 'bx bx-up-arrow-alt'],
            ['label' => 'Select Students', 'url' => '#', 'icon' => 'bx bx-list-ul']
        ]" />
        <h6 class="mb-0 text-uppercase">PROMOTE STUDENTS - SELECT STUDENTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-list-ul me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Select Students to Promote</h5>
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

                        <!-- Filter Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-filter me-2"></i> Filter Students
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('school.promote-students.create') }}" id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="class_id" class="form-label fw-bold">Class</label>
                                            <select class="form-select" id="class_id" name="class_id">
                                                <option value="">All Classes</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="stream_id" class="form-label fw-bold">Stream</label>
                                            <select class="form-select" id="stream_id" name="stream_id">
                                                <option value="">All Streams</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="academic_year_id" class="form-label fw-bold">Academic Year</label>
                                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                                <option value="">All Academic Years</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12 d-flex align-items-end">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-search me-1"></i> Filter
                                                </button>
                                                <a href="{{ route('school.promote-students.create') }}" class="btn btn-outline-secondary">
                                                    <i class="bx bx-refresh me-1"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Promotion Form -->
                        <form action="{{ route('school.promote-students.store') }}" method="POST" id="promoteForm">
                            @csrf
                            
                            <!-- Students Selection -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="bx bx-user me-2"></i> Select Students
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if(count($students) > 0)
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                                <i class="bx bx-check-square me-1"></i> Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                                <i class="bx bx-square me-1"></i> Deselect All
                                            </button>
                                            <span class="ms-3 text-muted" id="selectedCount">0 students selected</span>
                                        </div>
                                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-hover">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th width="50">
                                                            <input type="checkbox" id="selectAllCheckbox">
                                                        </th>
                                                        <th>Admission No.</th>
                                                        <th>Name</th>
                                                        <th>Class</th>
                                                        <th>Stream</th>
                                                        <th>Academic Year</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($students as $student)
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="students[]" value="{{ $student->id }}" class="student-checkbox">
                                                            </td>
                                                            <td>{{ $student->admission_number }}</td>
                                                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                            <td>{{ $student->class ? $student->class->name : 'N/A' }}</td>
                                                            <td>{{ $student->stream ? $student->stream->name : 'N/A' }}</td>
                                                            <td>{{ $student->academicYear ? $student->academicYear->year_name : 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-1"></i> No students found. Please adjust your filters.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if(count($students) > 0)
                                <!-- Promotion Details -->
                                <div class="card border-success mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-arrow-to-right me-2"></i> Promotion Details
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="new_class_id" class="form-label fw-bold">New Class <span class="text-danger">*</span></label>
                                                <select class="form-select @error('new_class_id') is-invalid @enderror" id="new_class_id" name="new_class_id" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ old('new_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('new_class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="new_stream_id" class="form-label fw-bold">New Stream</label>
                                                <select class="form-select @error('new_stream_id') is-invalid @enderror" id="new_stream_id" name="new_stream_id">
                                                    <option value="">Select Stream</option>
                                                </select>
                                                @error('new_stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="new_academic_year_id" class="form-label fw-bold">New Academic Year <span class="text-danger">*</span></label>
                                                <select class="form-select @error('new_academic_year_id') is-invalid @enderror" id="new_academic_year_id" name="new_academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ old('new_academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('new_academic_year_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="promotion_date" class="form-label fw-bold">Promotion Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('promotion_date') is-invalid @enderror" id="promotion_date" name="promotion_date" value="{{ old('promotion_date', date('Y-m-d')) }}" required>
                                                @error('promotion_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="notes" class="form-label fw-bold">Notes</label>
                                                <input type="text" class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Optional notes">
                                                @error('notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('school.promote-students.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-success" id="promoteBtn">
                                        <i class="bx bx-up-arrow-alt me-1"></i> Promote Selected Students
                                    </button>
                                </div>
                            @else
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('school.promote-students.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                </div>
                            @endif
                        </form>
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
    // Update selected count
    function updateSelectedCount() {
        const count = $('.student-checkbox:checked').length;
        $('#selectedCount').text(count + ' student(s) selected');
        $('#promoteBtn').prop('disabled', count === 0);
    }

    // Select all checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    // Individual checkboxes
    $('.student-checkbox').on('change', function() {
        updateSelectedCount();
        // Update select all checkbox state
        const total = $('.student-checkbox').length;
        const checked = $('.student-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', total === checked);
    });

    // Select all button
    $('#selectAllBtn').on('click', function() {
        $('.student-checkbox').prop('checked', true);
        $('#selectAllCheckbox').prop('checked', true);
        updateSelectedCount();
    });

    // Deselect all button
    $('#deselectAllBtn').on('click', function() {
        $('.student-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        updateSelectedCount();
    });

    // Load streams for class
    $('#class_id, #new_class_id').on('change', function() {
        const classId = $(this).val();
        const targetSelect = $(this).attr('id') === 'class_id' ? '#stream_id' : '#new_stream_id';
        loadStreamsForClass(classId, targetSelect);
    });

    function loadStreamsForClass(classId, targetSelect) {
        const streamSelect = $(targetSelect);
        
        if (!classId) {
            streamSelect.empty().append('<option value="">All Streams</option>');
            if (targetSelect === '#new_stream_id') {
                streamSelect.append('<option value="">Select Stream</option>');
            }
            return;
        }

        streamSelect.prop('disabled', true);

        $.ajax({
            url: '{{ route("school.api.students.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            success: function(response) {
                streamSelect.empty();
                if (targetSelect === '#stream_id') {
                    streamSelect.append('<option value="">All Streams</option>');
                } else {
                    streamSelect.append('<option value="">Select Stream</option>');
                }

                if (response.streams && response.streams.length > 0) {
                    response.streams.forEach(function(stream) {
                        streamSelect.append(`<option value="${stream.id}">${stream.name}</option>`);
                    });
                }
                streamSelect.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.empty().append('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', false);
            }
        });
    }

    // Form submission validation
    $('#promoteForm').on('submit', function(e) {
        const selectedCount = $('.student-checkbox:checked').length;
        if (selectedCount === 0) {
            e.preventDefault();
            alert('Please select at least one student to promote.');
            return false;
        }
    });

    // Initial count update
    updateSelectedCount();
});
</script>
@endpush

