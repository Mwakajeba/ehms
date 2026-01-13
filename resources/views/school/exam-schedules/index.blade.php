@extends('layouts.main')

@section('title', 'Exam Schedules')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Schedules', 'url' => '#', 'icon' => 'bx bx-calendar-event']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-calendar-event me-1 font-22 text-success"></i>
                                <span class="h5 mb-0 text-success">Exam Schedules Management</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-schedules.create') }}" class="btn btn-success">
                                    <i class="bx bx-plus me-1"></i> Create Exam Schedule
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="examTypeFilter" class="form-label">Exam Type</label>
                                <select class="form-select" id="examTypeFilter">
                                    <option value="">All Exam Types</option>
                                    @foreach($examTypes as $examType)
                                        <option value="{{ $examType->id }}">{{ $examType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="academicYearFilter" class="form-label">Academic Year</label>
                                <select class="form-select" id="academicYearFilter">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $academicYear)
                                        <option value="{{ $academicYear->id }}">{{ $academicYear->year_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="published">Published</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" id="searchBtn">
                                    <i class="bx bx-search me-1"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="examSchedulesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Exam Name</th>
                                        <th>Exam Type</th>
                                        <th>Academic Year</th>
                                        <th>Term</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Papers</th>
                                        <th>Status</th>
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
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .badge {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#examSchedulesTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("school.exam-schedules.index") }}',
                type: 'GET',
                data: function(d) {
                    d.exam_type_id = $('#examTypeFilter').val();
                    d.academic_year_id = $('#academicYearFilter').val();
                    d.status = $('#statusFilter').val();
                },
                dataSrc: 'data'
            },
            columns: [
                { data: 'id' },
                { data: 'exam_name' },
                { data: 'exam_type' },
                { data: 'academic_year' },
                { data: 'term' },
                { data: 'start_date' },
                { data: 'end_date' },
                { 
                    data: 'papers',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                { 
                    data: 'status',
                    orderable: false,
                    searchable: false
                },
                { 
                    data: 'actions', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: "No exam schedules found"
            }
        });

        $('#searchBtn').on('click', function() {
            table.ajax.reload();
        });

        // Toggle papers details
        $(document).on('click', '.toggle-papers', function() {
            const papersList = $(this).siblings('.papers-list');
            papersList.slideToggle();
        });

        // Handle publish button click
        $(document).on('click', '.publish-btn', function() {
            const scheduleId = $(this).data('schedule-id');
            const $btn = $(this);
            
            if (!confirm('Are you sure you want to publish this exam schedule to parents? This action cannot be undone.')) {
                return;
            }

            // Disable button and show loading
            $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');

            $.ajax({
                url: '{{ route("school.exam-schedules.publish", ":hashid") }}'.replace(':hashid', scheduleId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Published!',
                                text: response.message,
                                confirmButtonColor: '#0d6efd'
                            });
                        } else {
                            alert(response.message);
                        }
                        
                        // Reload table to update status
                        table.ajax.reload(null, false);
                    } else {
                        alert(response.message || 'Failed to publish exam schedule.');
                        $btn.prop('disabled', false).html('<i class="bx bx-send"></i>');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to publish exam schedule. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonColor: '#0d6efd'
                        });
                    } else {
                        alert(errorMessage);
                    }
                    
                    $btn.prop('disabled', false).html('<i class="bx bx-send"></i>');
                }
            });
        });
    });
</script>
@endpush

