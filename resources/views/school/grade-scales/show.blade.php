@extends('layouts.main')

@section('title', 'View Grade Scale')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Grade Scales', 'url' => route('school.grade-scales.index'), 'icon' => 'bx bx-star'],
            ['label' => 'View', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">VIEW GRADE SCALE</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-star me-1 font-22 text-danger"></i></div>
                                <h5 class="mb-0 text-danger">{{ $gradeScale->name }}</h5>
                            </div>
                            <div>
                                <a href="{{ route('school.grade-scales.edit', $gradeScale->id) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit me-1"></i>Edit
                                </a>
                                <a href="{{ route('school.grade-scales.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Grade Scales
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Grade Scale Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i>Grade Scale Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p><strong>Name:</strong> {{ $gradeScale->name }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Academic Year:</strong> {{ $gradeScale->academicYear ? $gradeScale->academicYear->year_name : 'N/A' }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Maximum Marks:</strong> {{ $gradeScale->max_marks }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Passed Average Point:</strong> {{ $gradeScale->passed_average_point }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Status:</strong>
                                                    @if($gradeScale->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Created:</strong> {{ $gradeScale->created_at->format('M d, Y H:i') }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Last Updated:</strong> {{ $gradeScale->updated_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        </div>
                                        @if($gradeScale->description)
                                            <div class="mt-3">
                                                <strong>Description:</strong>
                                                <p class="mt-1">{{ $gradeScale->description }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-stats me-1"></i>Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="fs-1 text-success">{{ $gradeScale->grades->count() }}</div>
                                                <div class="text-muted">Grade Ranges</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="fs-1 text-primary">{{ $gradeScale->max_marks }}</div>
                                                <div class="text-muted">Max Marks</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grades Table -->
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="bx bx-list-ul me-1"></i>Grade Ranges</h6>
                            </div>
                            <div class="card-body">
                                @if($gradeScale->grades->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Grade Letter</th>
                                                    <th>Grade Name</th>
                                                    <th>Min Marks</th>
                                                    <th>Max Marks</th>
                                                    <th>Grade Point</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($gradeScale->grades->sortBy('sort_order') as $grade)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td><strong>{{ $grade->grade_letter }}</strong></td>
                                                        <td>{{ $grade->grade_name }}</td>
                                                        <td>{{ number_format($grade->min_marks, 2) }}</td>
                                                        <td>{{ number_format($grade->max_marks, 2) }}</td>
                                                        <td>{{ $grade->grade_point ? number_format($grade->grade_point, 2) : '-' }}</td>
                                                        <td>{{ $grade->remarks ?: '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bx bx-info-circle fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No grade ranges defined for this scale.</p>
                                    </div>
                                @endif
                            </div>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    .badge {
        font-size: 0.75rem;
    }
</style>
@endpush