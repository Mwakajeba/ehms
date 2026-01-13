@extends('layouts.main')

@section('title', 'Academic Year Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Management', 'url' => route('school.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => route('school.academic-years.index'), 'icon' => 'bx bx-calendar'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">ACADEMIC YEAR DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-calendar me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">{{ $academicYear->year_name }}</h5>
                                @if($academicYear->is_current)
                                    <span class="badge bg-success ms-2">Current Academic Year</span>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('school.academic-years.edit', $academicYear) }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                @if(!$academicYear->is_current)
                                    <form action="{{ route('school.academic-years.set-current', $academicYear) }}" method="POST" class="d-inline ms-2">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to set this academic year as current?')">
                                            <i class="bx bx-check-circle me-1"></i> Set as Current
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Academic Year Name</label>
                                    <p class="form-control-plaintext">{{ $academicYear->year_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Start Date</label>
                                    <p class="form-control-plaintext">
                                        {{ $academicYear->start_date ? $academicYear->start_date->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">End Date</label>
                                    <p class="form-control-plaintext">
                                        {{ $academicYear->end_date ? $academicYear->end_date->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <p class="form-control-plaintext">
                                        @if($academicYear->is_current)
                                            <span class="badge bg-success">Active (Current)</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Students Assigned</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-info">{{ $academicYear->students_count ?? 0 }} Students</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Duration</label>
                                    <p class="form-control-plaintext">
                                        @if($academicYear->start_date && $academicYear->end_date)
                                            {{ $academicYear->start_date->diffInDays($academicYear->end_date) }} days
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created</label>
                                    <p class="form-control-plaintext">{{ $academicYear->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated</label>
                                    <p class="form-control-plaintext">{{ $academicYear->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students in this Academic Year -->
                @if($academicYear->students && $academicYear->students->count() > 0)
                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="bx bx-group me-1 font-22 text-primary"></i>
                                    <h5 class="mb-0 text-primary d-inline">Students in this Academic Year</h5>
                                    <span class="badge bg-info ms-2">{{ $academicYear->students->count() }}</span>
                                </div>
                            </div>
                            <hr />

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Admission No.</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Gender</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($academicYear->students as $student)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $student->admission_number ?? 'N/A' }}</td>
                                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                <td>{{ $student->class->name ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                                                <td>
                                                    <a href="{{ route('school.students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-show"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('school.academic-years.edit', $academicYear) }}" class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i> Edit Academic Year
                            </a>

                            @if(!$academicYear->is_current)
                                <form action="{{ route('school.academic-years.set-current', $academicYear) }}" method="POST">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to set this academic year as current?')">
                                        <i class="bx bx-check-circle me-1"></i> Set as Current
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('school.academic-years.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                @if(($academicYear->students_count ?? 0) == 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0 text-danger"><i class="bx bx-trash me-1"></i> Danger Zone</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Since no students are assigned to this academic year, you can delete it permanently.
                            </p>
                            <form action="{{ route('school.academic-years.destroy', $academicYear) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this academic year? This action cannot be undone.')">
                                    <i class="bx bx-trash me-1"></i> Delete Academic Year
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection