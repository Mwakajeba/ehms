@extends('layouts.main')

@section('title', 'Academic Reports')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Academic Reports', 'url' => '#', 'icon' => 'bx bx-book']
        ]" />
        <h6 class="mb-0 text-uppercase">ACADEMIC REPORTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-book me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Academic Reports</h5>
                        </div>
                        <hr />

                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            Academic Reports functionality will be implemented here. This will include:
                            <ul class="mb-0 mt-2">
                                <li>Class-wise performance</li>
                                <li>Subject-wise analysis</li>
                                <li>Grade distribution</li>
                                <li>Examination results</li>
                                <li>Academic trends</li>
                            </ul>
                        </div>

                        <div class="text-center py-5">
                            <i class="bx bx-book fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Coming Soon</h5>
                            <p class="text-muted">Academic Reports module is under development</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection