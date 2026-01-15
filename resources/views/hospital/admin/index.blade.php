@extends('layouts.main')

@section('title', 'Hospital Admin')

@push('styles')
<style>
    .admin-card {
        position: relative;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        cursor: pointer;
    }
    
    .admin-card:hover {
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
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Hospital Admin', 'url' => '#', 'icon' => 'bx bx-cog']
            ]" />
            <h6 class="mb-0 text-uppercase">HOSPITAL ADMINISTRATION</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Departments Card -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card admin-card h-100" onclick="window.location.href='{{ route('hospital.admin.departments.index') }}'">
                        <div class="count-badge bg-primary">{{ number_format($stats['departments']['total'] ?? 0) }}</div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-buildings text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">Departments</h5>
                            <p class="card-text">Manage hospital departments, types, and configurations.</p>
                            <div class="mt-3">
                                <span class="badge bg-success">{{ $stats['departments']['active'] ?? 0 }} Active</span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('hospital.admin.departments.index') }}" class="btn btn-primary">
                                    <i class="bx bx-buildings me-1"></i>Manage Departments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deletion Requests Card -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card admin-card h-100" onclick="window.location.href='{{ route('hospital.admin.deletion-requests.index') }}'">
                        <div class="count-badge bg-warning">{{ number_format($stats['deletion_requests']['pending'] ?? 0) }}</div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-trash text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">Deletion Requests</h5>
                            <p class="card-text">Review and approve/reject patient deletion requests.</p>
                            <div class="mt-3">
                                <span class="badge bg-warning">{{ $stats['deletion_requests']['pending'] ?? 0 }} Pending</span>
                                <span class="badge bg-secondary">{{ $stats['deletion_requests']['total'] ?? 0 }} Total</span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('hospital.admin.deletion-requests.index') }}" class="btn btn-warning">
                                    <i class="bx bx-trash me-1"></i>View Requests
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services & Products Card (Placeholder for future) -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card admin-card h-100" style="opacity: 0.6;">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-list-ul text-info" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">Services & Products</h5>
                            <p class="card-text">Manage hospital services and products (Coming Soon).</p>
                            <div class="mt-3">
                                <button class="btn btn-info" disabled>
                                    <i class="bx bx-list-ul me-1"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users & Roles Card (Placeholder for future) -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card admin-card h-100" style="opacity: 0.6;">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-user-circle text-secondary" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title">Users & Roles</h5>
                            <p class="card-text">Manage hospital users and role assignments (Coming Soon).</p>
                            <div class="mt-3">
                                <button class="btn btn-secondary" disabled>
                                    <i class="bx bx-user-circle me-1"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bx bx-info-circle me-2"></i>Hospital Administration
                        </h6>
                        <p class="mb-0">
                            The Hospital Administration module provides tools to manage departments, review patient deletion requests, 
                            and configure hospital settings. Use the cards above to access different administrative functions.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
