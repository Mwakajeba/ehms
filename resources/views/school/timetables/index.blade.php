@extends('layouts.main')

@section('title', 'School Timetables')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Timetables', 'url' => '#', 'icon' => 'bx bx-time-five']
        ]" />
        <h6 class="mb-0 text-uppercase">SCHOOL TIMETABLES</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-time-five me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Manage Timetables</h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-success me-2" id="generateTeacherTimetablesBtn">
                                    <i class="bx bx-user-plus me-1"></i> Generate Teacher Timetables
                                </button>
                                <a href="{{ route('school.timetables.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Create New Timetable
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Academic Year</label>
                                <select class="form-select form-select-sm" id="academic_year_id">
                                    <option value="">All Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ isset($currentAcademicYear) && $currentAcademicYear->id == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Class</label>
                                <select class="form-select form-select-sm" id="class_id">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Type</label>
                                <select class="form-select form-select-sm" id="timetable_type">
                                    <option value="">All Types</option>
                                    <option value="class">Class</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="teacher_on_duty">Teacher on Duty</option>
                                    <option value="room">Room</option>
                                    <option value="master">Master</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Status</label>
                                <select class="form-select form-select-sm" id="status">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="approved">Approved</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="timetables-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Academic Year</th>
                                        <th>Class/Stream</th>
                                        <th>Timetable Type</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
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
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#timetables-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.timetables.data") }}',
                data: function(d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.class_id = $('#class_id').val();
                    d.timetable_type = $('#timetable_type').val();
                    d.status = $('#status').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    var errorMessage = 'Failed to load timetables. ';
                    if (xhr.status === 500) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response && response.error) {
                                errorMessage += response.error;
                            } else if (response && response.message) {
                                errorMessage += response.message;
                            } else {
                                errorMessage += 'Server error occurred.';
                            }
                        } catch (e) {
                            errorMessage += 'Server error occurred.';
                        }
                    } else if (xhr.status === 404) {
                        errorMessage += 'Route not found.';
                    } else {
                        errorMessage += 'Please check the console for details.';
                    }
                    
                    // Show error in table
                    $('#timetables-table tbody').html(
                        '<tr><td colspan="8" class="text-center text-danger">' + 
                        '<i class="bx bx-error-circle me-2"></i>' + errorMessage + 
                        '</td></tr>'
                    );
                    
                    // Show toastr notification if available
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage, 'Error Loading Timetables');
                    } else {
                        alert(errorMessage);
                    }
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'academic_year_name', name: 'academic_year_name' },
                { data: 'class_stream', name: 'class_stream' },
                { data: 'type_badge', name: 'timetable_type', orderable: false },
                { data: 'status_badge', name: 'status', orderable: false },
                { data: 'creator_name', name: 'creator_name', defaultContent: 'N/A' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'asc']],
            pageLength: 25,
            language: {
                processing: '<i class="bx bx-loader-alt bx-spin"></i> Loading...',
                emptyTable: 'No timetables found. <a href="{{ route("school.timetables.create") }}" class="btn btn-sm btn-primary ms-2">Create New Timetable</a>',
                zeroRecords: 'No matching timetables found'
            }
        });

        // Apply filters on change
        $('#academic_year_id, #class_id, #timetable_type, #status').on('change', function() {
            table.ajax.reload();
        });

        // Generate Teacher Timetables
        $('#generateTeacherTimetablesBtn').on('click', function() {
            const academicYearId = $('#academic_year_id').val();
            
            if (!academicYearId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Academic Year Required',
                        text: 'Please select an academic year first.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Please select an academic year first.');
                }
                return;
            }

            const btn = $(this);
            const originalText = btn.html();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Generate Teacher Timetables?',
                    text: 'This will create individual teacher timetables for all teachers assigned in class timetables for the selected academic year. Continue?',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Generate',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        generateTeacherTimetables(academicYearId, btn, originalText);
                    }
                });
            } else {
                if (confirm('Generate teacher timetables for the selected academic year?')) {
                    generateTeacherTimetables(academicYearId, btn, originalText);
                }
            }
        });

        function generateTeacherTimetables(academicYearId, btn, originalText) {
            btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Generating...');
            
            $.ajax({
                url: '{{ route("school.timetables.generate-teacher-timetables") }}',
                type: 'POST',
                data: {
                    academic_year_id: academicYearId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                table.ajax.reload();
                            });
                        } else {
                            alert(response.message);
                            table.ajax.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to generate teacher timetables',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(response.message || 'Failed to generate teacher timetables');
                        }
                    }
                    btn.prop('disabled', false).html(originalText);
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to generate teacher timetables';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                    btn.prop('disabled', false).html(originalText);
                }
            });
        }

        // Publish timetable
        $(document).on('click', '.publish-timetable', function() {
            var hashId = $(this).data('id');
            var publishBtn = $(this);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Publish Timetable?',
                    text: 'This will make the timetable visible to students and parents.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Publish',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        publishBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');
                        
                        $.ajax({
                            url: '{{ url("school/timetables") }}/' + hashId + '/publish',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Published!',
                                        text: response.message || 'Timetable published successfully',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        table.ajax.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to publish timetable',
                                        confirmButtonText: 'OK'
                                    });
                                    publishBtn.prop('disabled', false).html('<i class="bx bx-check-circle"></i>');
                                }
                            },
                            error: function(xhr) {
                                var error = xhr.responseJSON?.message || 'Failed to publish timetable';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error,
                                    confirmButtonText: 'OK'
                                });
                                publishBtn.prop('disabled', false).html('<i class="bx bx-check-circle"></i>');
                            }
                        });
                    }
                });
            } else {
                if (confirm('Publish this timetable?')) {
                    $.ajax({
                        url: '{{ url("school/timetables") }}/' + hashId + '/publish',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message || 'Timetable published successfully');
                                table.ajax.reload();
                            } else {
                                alert(response.message || 'Failed to publish timetable');
                            }
                        },
                        error: function(xhr) {
                            var error = xhr.responseJSON?.message || 'Failed to publish timetable';
                            alert(error);
                        }
                    });
                }
            }
        });

        // Delete timetable
        $(document).on('click', '.delete-timetable', function() {
            var hashId = $(this).data('id');
            var row = $(this).closest('tr');
            var deleteBtn = $(this);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Timetable?',
                    text: 'Are you sure you want to delete this timetable? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url("school/timetables") }}/' + hashId,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.row(row).remove().draw();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message || 'Timetable deleted successfully',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.error || 'Failed to delete timetable',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var error = xhr.responseJSON?.error || 'Failed to delete timetable';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error,
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            } else {
                // Fallback to confirm if SweetAlert is not available
                if (confirm('Are you sure you want to delete this timetable?')) {
                    $.ajax({
                        url: '{{ url("school/timetables") }}/' + hashId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                table.row(row).remove().draw();
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(response.message || 'Timetable deleted successfully');
                                } else {
                                    alert(response.message || 'Timetable deleted successfully');
                                }
                            } else {
                                if (typeof toastr !== 'undefined') {
                                    toastr.error(response.error || 'Failed to delete timetable');
                                } else {
                                    alert(response.error || 'Failed to delete timetable');
                                }
                            }
                        },
                        error: function(xhr) {
                            var error = xhr.responseJSON?.error || 'Failed to delete timetable';
                            if (typeof toastr !== 'undefined') {
                                toastr.error(error);
                            } else {
                                alert(error);
                            }
                        }
                    });
                }
            }
        });
    });
</script>
@endpush

