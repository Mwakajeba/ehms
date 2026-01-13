@extends('layouts.main')

@section('title', 'Academic Years Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Management', 'url' => route('school.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => '#', 'icon' => 'bx bx-calendar']
        ]" />
        <h6 class="mb-0 text-uppercase">ACADEMIC YEARS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-calendar me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Academic Years</h5>
                            </div>
                            <a href="{{ route('school.academic-years.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add Academic Year
                            </a>
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

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Academic Year</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Students Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($academicYears as $academicYear)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $academicYear->year_name }}</strong>
                                                @if($academicYear->is_current)
                                                    <span class="badge bg-success ms-2">Current</span>
                                                @endif
                                            </td>
                                            <td>{{ $academicYear->start_date ? $academicYear->start_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $academicYear->end_date ? $academicYear->end_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                @if($academicYear->is_current)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $academicYear->students_count ?? 0 }} Students</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('school.academic-years.show', $academicYear) }}" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                    <a href="{{ route('school.academic-years.edit', $academicYear) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    @if($academicYear->is_current)
                                                        <button type="button" class="btn btn-sm btn-success" title="Current Academic Year" disabled>
                                                            <i class="bx bx-check-circle"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-success" title="Set as Current" onclick="setAsCurrent('{{ $academicYear->id }}', '{{ addslashes($academicYear->year_name) }}')">
                                                            <i class="bx bx-check-circle"></i>
                                                        </button>
                                                    @endif
                                                    @if(($academicYear->students_count ?? 0) == 0)
                                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="deleteAcademicYear('{{ $academicYear->id }}', '{{ addslashes($academicYear->year_name) }}')">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-calendar font-48 text-muted mb-2"></i>
                                                    <h6 class="text-muted">No Academic Years Found</h6>
                                                    <p class="text-muted mb-3">Get started by creating your first academic year</p>
                                                    <a href="{{ route('school.academic-years.create') }}" class="btn btn-primary">
                                                        <i class="bx bx-plus me-1"></i> Add Academic Year
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($academicYears->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $academicYears->links() }}
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Set as Current functionality with SweetAlert2
    function setAsCurrent(id, yearName) {
        Swal.fire({
            title: 'Set as Current Academic Year?',
            html: `Are you sure you want to set <strong>${yearName}</strong> as the current academic year?<br><br>This will unset any other academic year that is currently marked as current.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bx bx-check-circle me-1"></i> Yes, Set as Current',
            cancelButtonText: '<i class="bx bx-x me-1"></i> Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Setting academic year as current',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create and submit form
                const form = $('<form>', {
                    'method': 'POST',
                    'action': `{{ url('/school/academic-years') }}/${id}/set-current`
                });
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                $('body').append(form);
                form.submit();
            }
        });
    }

    // Delete Academic Year functionality with SweetAlert2
    function deleteAcademicYear(id, yearName) {
        Swal.fire({
            title: 'Delete Academic Year?',
            html: `Are you sure you want to delete <strong>${yearName}</strong>?<br><br><span class="text-danger">This action cannot be undone!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete',
            cancelButtonText: '<i class="bx bx-x me-1"></i> Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the academic year',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create and submit form
                const form = $('<form>', {
                    'method': 'POST',
                    'action': `{{ url('/school/academic-years') }}/${id}`
                });
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
                $('body').append(form);
                form.submit();
            }
        });
    }
</script>
@endpush