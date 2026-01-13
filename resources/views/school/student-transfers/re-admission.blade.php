@extends('layouts.main')

@section('title', 'Student Re-admission')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Student Transfers', 'url' => route('school.student-transfers.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'Re-admission', 'url' => '#', 'icon' => 'bx bx-refresh']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT RE-ADMISSION</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-refresh me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Student Re-admission</h5>
                        </div>
                        <hr />

                        @if($transferredStudents->count() > 0)
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                Found <strong>{{ $transferredStudents->count() }}</strong> student(s) with transferred out status available for re-admission.
                            </div>

                            <div class="row">
                                @foreach($transferredStudents as $student)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-warning h-100">
                                            <div class="card-header bg-warning text-white">
                                                <h6 class="mb-0">
                                                    <i class="bx bx-user me-2"></i>
                                                    {{ $student->first_name }} {{ $student->last_name }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <strong>Admission No:</strong> {{ $student->admission_number }}
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Class:</strong>
                                                    {{ $student->class ? $student->class->name : 'N/A' }}
                                                    {{ $student->stream ? '(' . $student->stream->name . ')' : '' }}
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Transferred To:</strong>
                                                    @if($student->transfers && $student->transfers->count() > 0)
                                                        {{ $student->transfers->first()->new_school ?? 'Unknown' }}
                                                    @else
                                                        Unknown
                                                    @endif
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Transfer Date:</strong>
                                                    @if($student->transfers && $student->transfers->count() > 0)
                                                        {{ $student->transfers->first()->transfer_date ? $student->transfers->first()->transfer_date->format('M d, Y') : 'N/A' }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Status:</strong>
                                                    <span class="badge bg-danger">Transferred Out</span>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <form action="{{ route('school.student-transfers.store') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="transfer_type" value="re_admission">
                                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                    <input type="hidden" name="transfer_date" value="{{ date('Y-m-d') }}">
                                                    <input type="hidden" name="previous_school" value="{{ $student->transfers && $student->transfers->count() > 0 ? $student->transfers->first()->new_school : 'Extended Break/Absence' }}">
                                                    <input type="hidden" name="new_school" value="{{ config('app.name') }}">
                                                    <input type="hidden" name="reason" value="re_admission">
                                                    <input type="hidden" name="academic_year_id" value="{{ $currentAcademicYear ? $currentAcademicYear->id : '' }}">

                                                    <button type="submit" class="btn btn-success btn-sm w-100"
                                                            onclick="return confirm('Are you sure you want to re-admit {{ $student->first_name }} {{ $student->last_name }}?')">
                                                        <i class="bx bx-refresh me-1"></i> Re-admit Student
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3 text-success">No Students Available for Re-admission</h4>
                                <p class="text-muted">All students are currently active or there are no transferred students in the system.</p>
                                <a href="{{ route('school.student-transfers.index') }}" class="btn btn-primary">
                                    <i class="bx bx-list-ul me-1"></i> View All Transfers
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
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.75rem;
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        font-weight: 600;
    }

    .card-footer {
        border-radius: 0 0 0.75rem 0.75rem !important;
        border-top: 2px solid rgba(0, 0, 0, 0.1);
        background-color: #f8f9fa;
    }

    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .col-md-6.col-lg-4 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Student re-admission page loaded');

    // Add loading state to forms
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');

        // Re-enable after 5 seconds as fallback
        setTimeout(function() {
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
        }, 5000);
    });
});
</script>
@endpush