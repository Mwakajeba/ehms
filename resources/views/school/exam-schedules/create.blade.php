@extends('layouts.main')

@section('title', 'Create Exam Schedule')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Schedules', 'url' => route('school.exam-schedules.index'), 'icon' => 'bx bx-calendar-event'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">Create Exam Schedule</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.exam-schedules.store') }}" method="POST" id="scheduleForm">
                            @csrf

                            <!-- Step 1: Basic Information -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Step 1: Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="exam_type_id" class="form-label">Exam Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('exam_type_id') is-invalid @enderror" id="exam_type_id" name="exam_type_id" required>
                                                <option value="">Select Exam Type</option>
                                                @foreach($examTypes as $examType)
                                                    <option value="{{ $examType->id }}" {{ old('exam_type_id') == $examType->id ? 'selected' : '' }}>
                                                        {{ $examType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('exam_type_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $academicYear)
                                                    <option value="{{ $academicYear->id }}" 
                                                        {{ old('academic_year_id', $currentAcademicYear->id ?? '') == $academicYear->id ? 'selected' : '' }}>
                                                        {{ $academicYear->year_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('academic_year_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="term" class="form-label">Term</label>
                                            <select class="form-select @error('term') is-invalid @enderror" id="term" name="term">
                                                <option value="">Select Term</option>
                                                <option value="I" {{ old('term') == 'I' ? 'selected' : '' }}>Term I</option>
                                                <option value="II" {{ old('term') == 'II' ? 'selected' : '' }}>Term II</option>
                                                <option value="III" {{ old('term') == 'III' ? 'selected' : '' }}>Term III</option>
                                            </select>
                                            @error('term')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" 
                                                   value="{{ old('start_date') }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" 
                                                   value="{{ old('end_date') }}" required>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="min_break_minutes" class="form-label">Minimum Break (Minutes)</label>
                                            <input type="number" class="form-control @error('min_break_minutes') is-invalid @enderror" 
                                                   id="min_break_minutes" name="min_break_minutes" 
                                                   value="{{ old('min_break_minutes', 30) }}" min="0" max="120">
                                            @error('min_break_minutes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-8 mb-3 d-flex align-items-end">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="has_half_day_exams" name="has_half_day_exams" value="1" {{ old('has_half_day_exams') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="has_half_day_exams">
                                                    Has Half-Day Exams
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Search and Select Courses -->
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-search me-1"></i> Step 2: Search and Select Courses</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="search_class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                            <select class="form-control class-select" id="search_class_id" name="class_id" required>
                                                <option value="">Select Class</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="search_stream_id" class="form-label">Stream (Optional)</label>
                                            <select class="form-control stream-select" id="search_stream_id" name="stream_id">
                                                <option value="">All Streams</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-info w-100" id="searchCoursesBtn">
                                                <i class="bx bx-search me-1"></i> Search Courses
                                            </button>
                                        </div>
                                    </div>

                                    <div id="coursesContainer" style="display: none;">
                                        <h6 class="mb-3">Available Courses</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="coursesTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="30">
                                                            <input type="checkbox" id="selectAllCourses">
                                                        </th>
                                                        <th>Subject Name</th>
                                                        <th>Class</th>
                                                        <th>Stream</th>
                                                        <th>Number of Students</th>
                                                        <th>Type</th>
                                                        <th>Date</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="coursesTableBody">
                                                    <!-- Courses will be loaded here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <hr />
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('school.exam-schedules.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to List
                                        </a>
                                        <button type="submit" class="btn btn-success" id="submitBtn">
                                            <i class="bx bx-save me-1"></i> Create Schedule
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for class and stream dropdowns
        if (typeof $.fn.select2 !== 'undefined') {
            $('#search_class_id, #search_stream_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).find('option:first').text();
                },
                allowClear: true
            });
        }

        // Load streams when class is selected
        $('#search_class_id').on('change', function() {
            loadStreams();
        });

        // Search courses
        $('#searchCoursesBtn').on('click', function() {
            searchCourses();
        });

        // Select all courses
        $('#selectAllCourses').on('change', function() {
            $('.course-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Auto-check checkbox when date, start time, and end time are filled
        $(document).on('change', '.course-date, .course-start-time, .course-end-time', function() {
            const $row = $(this).closest('tr');
            const $checkbox = $row.find('.course-checkbox');
            const date = $row.find('.course-date').val();
            const startTime = $row.find('.course-start-time').val();
            const endTime = $row.find('.course-end-time').val();
            
            // Auto-check if all required fields are filled
            if (date && startTime && endTime) {
                $checkbox.prop('checked', true);
            }
        });

        // Form submission validation
        $('#scheduleForm').on('submit', function(e) {
            const checkedBoxes = $('.course-checkbox:checked');
            const filledRows = [];
            
            // Check for rows with filled dates/times but unchecked boxes
            $('.course-checkbox').each(function() {
                const $row = $(this).closest('tr');
                const date = $row.find('.course-date').val();
                const startTime = $row.find('.course-start-time').val();
                const endTime = $row.find('.course-end-time').val();
                const isChecked = $(this).prop('checked');
                
                if (date && startTime && endTime && !isChecked) {
                    filledRows.push($row.find('td:eq(1)').text().trim()); // Subject name
                }
            });
            
            if (filledRows.length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Unchecked Courses',
                    html: 'You have filled in dates and times for the following courses, but they are not checked:<br><strong>' + 
                          filledRows.join(', ') + '</strong><br><br>Please check the boxes for courses you want to save, or clear the dates/times.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // Log what's being submitted for debugging
            console.log('Form submission:', {
                checked_boxes: checkedBoxes.length,
                assignment_ids: checkedBoxes.map(function() { return $(this).val(); }).get(),
            });
        });

        function loadStreams() {
            const classId = $('#search_class_id').val();
            const $streamSelect = $('#search_stream_id');
            
            if (!classId) {
                $streamSelect.empty();
                $streamSelect.append('<option value="">All Streams</option>');
                if (typeof $.fn.select2 !== 'undefined') {
                    $streamSelect.trigger('change');
                }
                return;
            }

            $.ajax({
                url: '{{ route("school.exam-schedules.api.get-streams", ":id") }}'.replace(':id', classId),
                method: 'GET',
                success: function(response) {
                    $streamSelect.empty();
                    // Always add "All Streams" as the first option
                    $streamSelect.append('<option value="">All Streams</option>');
                    
                    if (response && response.streams && response.streams.length > 0) {
                        response.streams.forEach(function(stream) {
                            $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                        });
                    }
                    
                    // Trigger change to update select2
                    if (typeof $.fn.select2 !== 'undefined') {
                        $streamSelect.trigger('change');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load streams:', error);
                    $streamSelect.empty();
                    $streamSelect.append('<option value="">All Streams</option>');
                    if (typeof $.fn.select2 !== 'undefined') {
                        $streamSelect.trigger('change');
                    }
                }
            });
        }

        function searchCourses() {
            const examTypeId = $('#exam_type_id').val();
            const academicYearId = $('#academic_year_id').val();
            const classId = $('#search_class_id').val();
            const streamId = $('#search_stream_id').val();

            if (!examTypeId || !academicYearId || !classId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please select Exam Type, Academic Year, and Class to search for courses.'
                });
                return;
            }

            $.ajax({
                url: '{{ route("school.exam-schedules.api.get-courses") }}',
                method: 'GET',
                data: {
                    exam_type_id: examTypeId,
                    academic_year_id: academicYearId,
                    class_id: classId,
                    stream_id: streamId
                },
                success: function(response) {
                    if (response.success && response.courses.length > 0) {
                        let tbody = '';
                        response.courses.forEach(function(course) {
                            tbody += `
                                <tr>
                                    <td>
                                        <input type="checkbox" class="course-checkbox" 
                                               name="assignment_ids[]" 
                                               value="${course.assignment_id}">
                                    </td>
                                    <td>${course.subject_name}</td>
                                    <td>${course.class_name}</td>
                                    <td>${course.stream_name || 'All Streams'}</td>
                                    <td>${course.number_of_students}</td>
                                    <td>
                                        <select class="form-control form-control-sm course-type" 
                                                name="course_types[${course.assignment_id}]" 
                                                data-assignment-id="${course.assignment_id}" required>
                                            <option value="theory">Theory</option>
                                            <option value="practical">Practical</option>
                                            <option value="oral">Oral</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm course-date" 
                                               name="course_dates[${course.assignment_id}]" 
                                               data-assignment-id="${course.assignment_id}" required>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm course-start-time" 
                                               name="course_start_times[${course.assignment_id}]" 
                                               data-assignment-id="${course.assignment_id}" required>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm course-end-time" 
                                               name="course_end_times[${course.assignment_id}]" 
                                               data-assignment-id="${course.assignment_id}" required>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#coursesTableBody').html(tbody);
                        $('#coursesContainer').show();
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'No Courses Found',
                            text: 'No courses found for the selected criteria.'
                        });
                        $('#coursesContainer').hide();
                    }
                },
                error: function(xhr) {
                    console.error('Failed to search courses', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to search courses. Please try again.'
                    });
                }
            });
        }
    });
</script>
@endpush

