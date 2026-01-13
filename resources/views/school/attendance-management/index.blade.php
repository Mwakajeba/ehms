@extends('layouts.main')

@section('title', 'Attendance Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Attendance Management', 'url' => '#', 'icon' => 'bx bx-check-circle']
        ]" />
        <h6 class="mb-0 text-uppercase">ATTENDANCE MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-check-circle me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Attendance Management Modules</h5>
                        </div>
                        <hr />

                        <!-- Attendance Management Submodules -->
                        <div class="row mb-4">
                            <!-- Content will be added here if needed -->
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
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    /* Notification badge positioning */
    .position-relative .badge {
        z-index: 10;
        font-size: 0.7rem;
        min-width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .border-success {
        border-color: #198754 !important;
    }

    .bg-success.bg-opacity-10 {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .bg-primary.bg-opacity-10 {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .card-title {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .card-text {
        font-size: 0.75rem;
        line-height: 1.2;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add any interactive functionality here
    console.log('Attendance Management module loaded');
});
</script>
@endpush