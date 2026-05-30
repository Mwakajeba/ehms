@extends('layouts.main')

@section('title', 'Hospital Reports')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reports', 'url' => '#', 'icon' => 'bx bx-file']
            ]" />
            <h6 class="mb-0 text-uppercase">HOSPITAL REPORTS</h6>
            <hr />

            <div class="mb-3">
                <a href="{{ route('hospital.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Hospital Management
                </a>
            </div>

            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-dark">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-3">
                                <i class="bx bx-volume-full text-dark" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">Audiology Report</h5>
                            <p class="card-text text-muted flex-grow-1">
                                Monthly-style audiology register by visiting date: payment mode, all service &amp; product inventory columns, and contact.
                            </p>
                            <a href="{{ route('hospital.reports.audiology') }}" class="btn btn-dark mt-auto">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i>Open Report
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-3">
                                <i class="bx bx-user-plus text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">Patient Registration Report</h5>
                            <p class="card-text text-muted flex-grow-1">
                                List of patients registered within a selected date range, with insurance and contact details.
                            </p>
                            <a href="{{ route('hospital.reports.patient-registration') }}" class="btn btn-primary mt-auto">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i>Open Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mb-0">
                <i class="bx bx-info-circle me-2"></i>
                More clinical, financial, and operational reports will be added here.
            </div>
        </div>
    </div>
@endsection
