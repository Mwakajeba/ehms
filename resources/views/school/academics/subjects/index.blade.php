@extends('layouts.main')

@section('title', 'Subjects Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Academics', 'url' => route('school.academics.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Subjects', 'url' => '#', 'icon' => 'bx bx-book']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBJECTS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">All Subjects</h5>
                        <a href="{{ route('school.academics.subjects.create') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Add New Subject
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($subjects->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="subjectsTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Short Name</th>
                                            <th>Type</th>
                                            <th>Credit Hours</th>
                                            <th>Sort Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjects as $subject)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="badge bg-info">{{ $subject->code }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $subject->name }}</strong>
                                                    @if($subject->description)
                                                        <br><small class="text-muted">{{ Str::limit($subject->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subject->short_name)
                                                        <span class="badge bg-light text-dark">{{ $subject->short_name }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subject->subject_type)
                                                        <span class="badge bg-primary">{{ ucfirst($subject->subject_type) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subject->credit_hours)
                                                        {{ $subject->credit_hours }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $subject->sort_order }}</span>
                                                </td>
                                                <td>
                                                    @if($subject->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('school.academics.subjects.show', $subject->hashid) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="View Details">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('school.academics.subjects.edit', $subject->hashid) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="Edit">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <form action="{{ route('school.academics.subjects.destroy', $subject->hashid) }}" 
                                                              method="POST" 
                                                              class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger" 
                                                                    title="Delete"
                                                                    onclick="return confirm('Are you sure you want to delete this subject?')">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-book display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">No Subjects Found</h5>
                                <p class="text-muted mb-4">There are no subjects in the system yet. Create your first subject to get started.</p>
                                <a href="{{ route('school.academics.subjects.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Create First Subject
                                </a>
                            </div>
                        @endif
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
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge {
        font-size: 0.75rem;
    }

    .btn-group .btn {
        margin-right: 2px;
        border-radius: 4px !important;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .card {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .alert-success {
        border-left: 4px solid #198754;
        background-color: #f8fff9;
    }

    .display-4 {
        font-size: 3rem;
    }

    /* DataTables custom styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#subjectsTable').DataTable({
            "pageLength": 25,
            "responsive": true,
            "order": [[6, "asc"]], // Sort by Sort Order by default
            "columnDefs": [
                { "orderable": false, "targets": [8] }, // Actions column not sortable
                { "width": "80px", "targets": [0] }, // # column width
                { "width": "100px", "targets": [1, 4, 6, 7] }, // Code, Type, Sort Order, Status
                { "width": "120px", "targets": [8] } // Actions column width
            ],
            "language": {
                "search": "Search subjects:",
                "lengthMenu": "Show _MENU_ subjects per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ subjects",
                "infoEmpty": "No subjects found",
                "infoFiltered": "(filtered from _MAX_ total subjects)",
                "emptyTable": "No subjects available",
                "zeroRecords": "No matching subjects found"
            }
        });

        // Handle delete confirmation
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const subjectName = form.closest('tr').find('td:nth-child(3) strong').text();

            if (confirm(`Are you sure you want to delete "${subjectName}"? This action cannot be undone.`)) {
                form[0].submit();
            }
        });
    });
</script>
@endpush