@extends('layouts.main')

@section('title', 'All Teacher Timetables')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Timetables', 'url' => route('school.timetables.index'), 'icon' => 'bx bx-time-five'],
            ['label' => 'All Teacher Timetables', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />
        <h6 class="mb-0 text-uppercase">ALL TEACHER TIMETABLES</h6>
        <hr />

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Filter by Academic Year</label>
                <select class="form-select form-select-sm" id="academicYearFilter">
                    <option value="all" {{ $academicYearId === 'all' ? 'selected' : '' }}>All Academic Years</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8 text-end">
                <a href="{{ route('school.timetables.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Timetables
                </a>
            </div>
        </div>

        @if($teacherTimetables->isEmpty())
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i>
                <strong>No teacher timetables found.</strong>
                @if($academicYearId !== 'all')
                    Try selecting a different academic year or <a href="{{ route('school.timetables.show-all-teachers', 'all') }}" class="alert-link">view all academic years</a>.
                @endif
            </div>
        @else
            <div class="row">
                @foreach($teacherTimetables as $timetable)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-user me-1"></i>
                                    {{ $timetable->name }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Academic Year:</strong> 
                                    {{ $timetable->academicYear ? $timetable->academicYear->year_name : 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Status:</strong>
                                    @php
                                        $statusBadges = [
                                            'draft' => 'secondary',
                                            'reviewed' => 'info',
                                            'approved' => 'success',
                                            'published' => 'primary'
                                        ];
                                        $badge = $statusBadges[$timetable->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($timetable->status) }}</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Created By:</strong> 
                                    {{ $timetable->creator ? $timetable->creator->name : 'N/A' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Created:</strong> 
                                    {{ $timetable->created_at->format('M d, Y') }}
                                </div>
                                <div class="mb-2">
                                    <strong>Entries:</strong> 
                                    {{ $timetable->entries->count() }} periods
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('school.timetables.show', $timetable->hashid) }}" class="btn btn-sm btn-info">
                                        <i class="bx bx-show me-1"></i> View
                                    </a>
                                    <a href="{{ route('school.timetables.edit', $timetable->hashid) }}" class="btn btn-sm btn-warning">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                    <a href="{{ route('school.timetables.print', $timetable->hashid) }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bx bx-printer me-1"></i> Print
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#academicYearFilter').on('change', function() {
            const academicYearId = $(this).val();
            const url = '{{ route("school.timetables.show-all-teachers", ":id") }}'.replace(':id', academicYearId);
            window.location.href = url;
        });
    });
</script>
@endpush

