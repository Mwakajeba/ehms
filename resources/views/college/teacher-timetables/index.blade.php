@extends('layouts.main')

@section('title', 'Teacher Timetables')

@section('content')
<div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 me-2">
                        <i class="bx bx-home-alt me-1"></i> Dashboard
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.index') }}" class="btn btn-light btn-sm rounded-pill px-3 mx-2">
                        <i class="bx bx-book-reader me-1"></i> College Management
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <span class="btn btn-primary btn-sm rounded-pill px-3 ms-2">
                        <i class="bx bx-user me-1"></i> Teacher Timetables
                    </span>
                </nav>
            </div>
        </div>

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="bx bx-user-circle me-2"></i>Teacher Timetables
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">College</a></li>
                            <li class="breadcrumb-item active">Teacher Timetables</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Teachers</p>
                                <h4 class="fs-22 fw-semibold mb-0">{{ $instructors->count() }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                    <i class="bx bx-user-circle text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Sessions</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-success">{{ $instructors->sum('timetable_slots_count') }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                    <i class="bx bx-calendar-check text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Current Year</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-info">{{ $currentAcademicYear->name ?? 'N/A' }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                    <i class="bx bx-calendar text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Avg Sessions/Teacher</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-warning">
                                    {{ $instructors->count() > 0 ? number_format($instructors->sum('timetable_slots_count') / $instructors->count(), 1) : 0 }}
                                </h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded fs-3">
                                    <i class="bx bx-bar-chart-alt-2 text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teachers List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-list-ul me-2"></i>Teachers with Assigned Classes
                                </h5>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('college.timetables.index') }}" class="btn btn-outline-primary">
                                    <i class="bx bx-calendar-alt me-1"></i> Program Timetables
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="text" class="form-control" id="searchTeacher" placeholder="Search teacher...">
                                </div>
                            </div>
                        </div>

                        <!-- Teachers Grid -->
                        <div class="row" id="teachersGrid">
                            @forelse($instructors as $instructor)
                                <div class="col-lg-4 col-md-6 mb-4 teacher-card" data-name="{{ strtolower($instructor->full_name) }}">
                                    <div class="card h-100 border shadow-sm hover-shadow">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="avatar-md me-3">
                                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-4">
                                                        {{ strtoupper(substr($instructor->first_name, 0, 1) . substr($instructor->last_name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1">{{ $instructor->full_name }}</h5>
                                                    <p class="text-muted mb-0 small">
                                                        <i class="bx bx-id-card me-1"></i>{{ $instructor->employee_number ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between mb-3 border-top border-bottom py-2">
                                                <div class="text-center">
                                                    <h5 class="mb-0 text-primary">{{ $instructor->timetable_slots_count }}</h5>
                                                    <small class="text-muted">Sessions</small>
                                                </div>
                                                <div class="text-center">
                                                    <h5 class="mb-0 text-success">
                                                        {{ $instructor->timetableSlots->pluck('course_id')->unique()->count() }}
                                                    </h5>
                                                    <small class="text-muted">Courses</small>
                                                </div>
                                                <div class="text-center">
                                                    <h5 class="mb-0 text-info">
                                                        @php
                                                            $hours = $instructor->timetableSlots->sum(function($slot) {
                                                                return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
                                                            });
                                                        @endphp
                                                        {{ number_format($hours, 1) }}
                                                    </h5>
                                                    <small class="text-muted">Hrs/Week</small>
                                                </div>
                                            </div>

                                            @if($instructor->department)
                                                <p class="mb-2 small">
                                                    <i class="bx bx-building me-1 text-muted"></i>
                                                    {{ $instructor->department->name }}
                                                </p>
                                            @endif
                                            @if($instructor->email)
                                                <p class="mb-3 small">
                                                    <i class="bx bx-envelope me-1 text-muted"></i>
                                                    {{ $instructor->email }}
                                                </p>
                                            @endif

                                            <div class="d-flex gap-2">
                                                <a href="{{ route('college.teacher-timetables.show', $instructor) }}" class="btn btn-primary btn-sm flex-grow-1">
                                                    <i class="bx bx-calendar me-1"></i> View Timetable
                                                </a>
                                                <a href="{{ route('college.teacher-timetables.export-pdf', $instructor) }}" class="btn btn-outline-danger btn-sm" title="Export PDF">
                                                    <i class="bx bx-file-blank"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="bx bx-user-x fs-1 text-muted"></i>
                                        <h5 class="mt-3 text-muted">No Teachers Found</h5>
                                        <p class="text-muted">No teachers have been assigned to any timetable slots yet.</p>
                                        <a href="{{ route('college.timetables.index') }}" class="btn btn-primary">
                                            <i class="bx bx-calendar-plus me-1"></i> Go to Timetables
                                        </a>
                                    </div>
                                </div>
                            @endforelse
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
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
    .avatar-md {
        width: 50px;
        height: 50px;
    }
    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchTeacher').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('.teacher-card').each(function() {
            var name = $(this).data('name');
            if (name.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
@endpush
