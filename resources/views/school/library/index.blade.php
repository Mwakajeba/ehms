@extends('layouts.main')

@section('title', 'School Digital Library / Learning Portal')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Digital Library', 'url' => '#', 'icon' => 'bx bx-library']
        ]" />
        <h6 class="mb-0 text-uppercase">SCHOOL DIGITAL LIBRARY / LEARNING PORTAL</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-library me-1 font-22 text-purple"></i></div>
                                <h5 class="mb-0 text-purple">Manage Library Materials</h5>
                            </div>
                            <div>
                                <a href="{{ route('school.library.create') }}" class="btn btn-purple">
                                    <i class="bx bx-plus me-1"></i> Upload Material
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Type</label>
                                <select class="form-select form-select-sm" id="type">
                                    <option value="">All Types</option>
                                    <option value="pdf_book">PDF Books</option>
                                    <option value="notes">Notes</option>
                                    <option value="past_paper">Past Papers</option>
                                    <option value="assignment">Assignments</option>
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
                                <label class="form-label small">Subject</label>
                                <select class="form-select form-select-sm" id="subject_id">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Status</label>
                                <select class="form-select form-select-sm" id="status">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-sm btn-secondary" id="clearFiltersBtn">
                                    <i class="bx bx-x me-1"></i> Clear Filters
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="library-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <th>File</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
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
    .text-purple {
        color: #6f42c1 !important;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }

    .btn-purple:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: white;
    }

    .border-purple {
        border-color: #6f42c1 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#library-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('school.library.data') }}",
                data: function(d) {
                    d.type = $('#type').val();
                    d.class_id = $('#class_id').val();
                    d.subject_id = $('#subject_id').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title', name: 'title' },
                { data: 'type_badge', name: 'type', orderable: false },
                { data: 'class_name', name: 'class_name' },
                { data: 'subject_name', name: 'subject_name' },
                { data: 'file_info', name: 'file_info', orderable: false },
                { data: 'status_badge', name: 'status', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']],
            pageLength: 25,
            language: {
                processing: '<i class="bx bx-loader-alt bx-spin font-22"></i>'
            }
        });

        // Filter on change
        $('#type, #class_id, #subject_id, #status').on('change', function() {
            table.draw();
        });

        // Clear filters
        $('#clearFiltersBtn').on('click', function() {
            $('#type, #class_id, #subject_id, #status').val('').trigger('change');
        });

        // Delete confirmation
        $(document).on('click', '.delete-material', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            var title = $(this).data('title');

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete: " + title,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'Library material has been deleted.',
                                'success'
                            );
                            table.draw();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Failed to delete library material.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

