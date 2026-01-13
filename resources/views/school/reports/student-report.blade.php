@extends('layouts.main')

@section('title', 'Student Reports')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Student Reports', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT REPORTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-group me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Student Reports</h5>
                        </div>
                        <hr />

                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Student Reports functionality will be implemented here. This will include:
                            <ul class="mb-0 mt-2">
                                <li>Student lists by class/stream</li>
                                <li>Student contact information</li>
                                <li>Student performance reports</li>
                                <li>Enrollment statistics</li>
                                <li>Demographic analysis</li>
                            </ul>
                        </div>

                        <div class="text-center py-5">
                            <i class="bx bx-group fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Coming Soon</h5>
                            <p class="text-muted">Student Reports module is under development</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection