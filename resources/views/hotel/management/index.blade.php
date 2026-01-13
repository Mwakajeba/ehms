@extends('layouts.main')

@section('title', 'Hotel Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Hotel & Property Management', 'url' => '#', 'icon' => 'bx bx-building-house'],
            ['label' => 'Hotel Management', 'url' => '#', 'icon' => 'bx bx-hotel']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Hotel Management Dashboard</h4>
                        <p class="card-subtitle text-muted">Manage your hotel operations, rooms, bookings, and guests</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-primary bg-gradient">
                                    <div class="avatar-title text-white">
                                        <i class="bx bx-building font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Properties</p>
                                <h4 class="mb-0">{{ number_format($totalProperties) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-success bg-gradient">
                                    <div class="avatar-title text-white">
                                        <i class="bx bx-bed font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Rooms</p>
                                <h4 class="mb-0">{{ number_format($totalRooms) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-info bg-gradient">
                                    <div class="avatar-title text-white">
                                        <i class="bx bx-check-circle font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted mb-0">Available Rooms</p>
                                <h4 class="mb-0">{{ number_format($availableRooms) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-warning bg-gradient">
                                    <div class="avatar-title text-white">
                                        <i class="bx bx-trending-up font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted mb-0">Occupancy Rate</p>
                                <h4 class="mb-0">{{ number_format($currentOccupancy, 1) }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rooms Occupied -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-gradient-warning bg-gradient">
                                    <div class="avatar-title text-white">
                                        <i class="bx bx-bed font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted mb-0">Rooms Occupied</p>
                                <h4 class="mb-0">{{ number_format($roomsOccupied ?? 0) }} / {{ number_format($totalRooms) }}</h4>
                                <p class="text-muted mb-0 small">Currently occupied</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Today's Bookings -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-gradient-lush bg-gradient">
                                    <div class="avatar-title text-white">
                                        <i class="bx bx-book-content font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted mb-0">Today's Bookings</p>
                                <h4 class="mb-0">TZS {{ number_format($todaysBookingsValue ?? 0, 2) }}</h4>
                                <p class="text-muted mb-0 small">Value ({{ $todaysBookingsCount ?? 0 }})</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-bed text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Room Management</h5>
                        <p class="text-muted">Manage rooms, rates, and availability</p>
                        <a href="{{ route('rooms.index') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>Manage Rooms
                        </a>
                        <div class="mt-2">
                            <a href="{{ route('properties.create') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-building-house me-1"></i>Create Property
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-calendar-check text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Booking Management</h5>
                        <p class="text-muted">Handle reservations and check-ins</p>
                        <a href="{{ route('bookings.index') }}" class="btn btn-success">
                            <i class="bx bx-plus me-1"></i>Manage Bookings
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-user text-info" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Guest Management</h5>
                        <p class="text-muted">Manage guest information and history</p>
                        <a href="{{ route('guests.index') }}" class="btn btn-info">
                            <i class="bx bx-plus me-1"></i>Manage Guests
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-wallet text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Hotel Expenses</h5>
                        <p class="text-muted">Record general and room-specific expenses</p>
                        <a href="{{ route('hotel.expenses.index') }}" class="btn btn-warning">
                            <i class="bx bx-plus me-1"></i>Manage Expenses
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Monthly Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary">{{ number_format($totalBookings) }}</h4>
                                <p class="text-muted mb-0">Bookings This Month</p>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">{{ number_format($totalGuests) }}</h4>
                                <p class="text-muted mb-0">Total Guests</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Revenue Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12">
                                <h4 class="text-success">{{ number_format($monthlyRevenue, 2) }} TZS</h4>
                                <p class="text-muted mb-0">Monthly Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
