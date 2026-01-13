@extends('layouts.main')

@section('title', $module . ' - Coming Soon')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => $module, 'url' => '#', 'icon' => 'bx bx-info-circle']
            ]" />
            <h6 class="mb-0 text-uppercase">{{ strtoupper($module) }}</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="bx bx-time-five text-warning" style="font-size: 5rem;"></i>
                            </div>
                            <h3 class="card-title mb-3">{{ $module }} Module</h3>
                            <p class="text-muted mb-4">{{ $description }}</p>
                            <div class="alert alert-info d-inline-block">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Coming Soon!</strong> This module is currently under development.
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('hospital.index') }}" class="btn btn-primary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Hospital Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
