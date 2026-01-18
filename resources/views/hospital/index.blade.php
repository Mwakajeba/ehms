@extends('layouts.main')

@section('title', 'Hospital Management')

@push('styles')
<style>
    .module-card {
        position: relative;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .count-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        color: white;
        z-index: 10;
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => '#', 'icon' => 'bx bx-plus-medical']
            ]" />
            <h6 class="mb-0 text-uppercase">HOSPITAL MANAGEMENT SYSTEM</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Hospital Management Dashboard</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row">
                                <!-- Reception -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['patients']['total'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user-plus text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Reception</h5>
                                            <p class="card-text">Patient registration, MRN generation, visit creation, and pre-billing.</p>
                                            <a href="{{ route('hospital.reception.index') }}" class="btn btn-primary">
                                                <i class="bx bx-user me-1"></i>Go to Reception
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cashier -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-success">{{ number_format($stats['bills']['pending'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-money text-success" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Cashier</h5>
                                            <p class="card-text">Bill management, payment processing (Cash, NHIF, CHF, Jubilee, Strategy, Mobile), and clearance.</p>
                                            <a href="{{ route('hospital.cashier.index') }}" class="btn btn-success">
                                                <i class="bx bx-money me-1"></i>Go to Cashier
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Triage -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['visits']['triage_pending'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-pulse text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Triage</h5>
                                            <p class="card-text">Record vital signs, assess patients, and route them to appropriate departments.</p>
                                            <a href="{{ route('hospital.triage.index') }}" class="btn btn-info">
                                                <i class="bx bx-pulse me-1"></i>Go to Triage
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Doctor -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-warning">{{ number_format($stats['visits']['doctor_pending'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user-md text-warning" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Doctor</h5>
                                            <p class="card-text">Consultations, diagnosis, treatment plans, prescriptions, and view lab/ultrasound results.</p>
                                            <a href="{{ route('hospital.doctor.index') }}" class="btn btn-warning">
                                                <i class="bx bx-user-md me-1"></i>Go to Doctor
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lab -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-danger">{{ number_format($stats['visits']['lab_pending'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-test-tube text-danger" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Lab</h5>
                                            <p class="card-text">Laboratory test results entry, status updates, and result management.</p>
                                            <a href="{{ route('hospital.lab.index') }}" class="btn btn-danger">
                                                <i class="bx bx-test-tube me-1"></i>Go to Lab
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ultrasound -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-secondary">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-scan text-secondary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Ultrasound</h5>
                                            <p class="card-text">Ultrasound examination results entry, image management, and result status.</p>
                                            <a href="{{ route('hospital.ultrasound.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-scan me-1"></i>Go to Ultrasound
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pharmacy -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['visits']['pharmacy_pending'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-capsule text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Pharmacy</h5>
                                            <p class="card-text">Medication dispensing, prescription management, and stock tracking.</p>
                                            <a href="{{ route('hospital.pharmacy.index') }}" class="btn btn-primary">
                                                <i class="bx bx-capsule me-1"></i>Go to Pharmacy
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dental -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-smile text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Dental</h5>
                                            <p class="card-text">Dental procedures, treatments, and patient dental records.</p>
                                            <a href="{{ route('hospital.dental.index') }}" class="btn btn-info">
                                                <i class="bx bx-smile me-1"></i>Go to Dental
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- RCH -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-success">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-heart text-success" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">RCH</h5>
                                            <p class="card-text">Reproductive and Child Health services management.</p>
                                            <a href="{{ route('hospital.rch.index') }}" class="btn btn-success">
                                                <i class="bx bx-heart me-1"></i>Go to RCH
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vaccine -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-warning">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-shield text-warning" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Vaccine</h5>
                                            <p class="card-text">Vaccination tracking, vaccine administration, and immunization records.</p>
                                            <a href="{{ route('hospital.vaccine.index') }}" class="btn btn-warning">
                                                <i class="bx bx-shield me-1"></i>Go to Vaccine
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Injection -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-danger">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-injection text-danger" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Injection</h5>
                                            <p class="card-text">Injection services, medication administration, and injection records.</p>
                                            <a href="{{ route('hospital.injection.index') }}" class="btn btn-danger">
                                                <i class="bx bx-injection me-1"></i>Go to Injection
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Family Planning -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-heart-circle text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Family Planning</h5>
                                            <p class="card-text">Family planning services, counseling, and contraceptive management.</p>
                                            <a href="{{ route('hospital.family-planning.index') }}" class="btn btn-primary">
                                                <i class="bx bx-heart-circle me-1"></i>Go to Family Planning
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hospital Admin -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-dark">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-cog text-dark" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Hospital Admin</h5>
                                            <p class="card-text">Manage departments, services, products, users, roles, and patient deletion requests.</p>
                                            <a href="{{ route('hospital.admin.index') }}" class="btn btn-dark">
                                                <i class="bx bx-cog me-1"></i>Go to Admin
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hospital Reports -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-secondary">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-file text-secondary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Hospital Reports</h5>
                                            <p class="card-text">Clinical, financial, operational reports, and audit logs.</p>
                                            <a href="{{ route('hospital.reports.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-file me-1"></i>View Reports
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="bx bx-info-circle me-2"></i>Hospital Management System
                                        </h6>
                                        <p class="mb-0">
                                            The Hospital Management System provides comprehensive features for patient registration, visit management, 
                                            billing, payment processing, department workflows, time tracking, and comprehensive reporting. 
                                            All modules are integrated to provide seamless patient flow from reception to discharge.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
