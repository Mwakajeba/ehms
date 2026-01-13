@extends('layouts.main')

@section('title', 'Booking Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Hotel & Property Management', 'url' => route('hotel.management.index'), 'icon' => 'bx bx-building-house'],
            ['label' => 'Booking Management', 'url' => '#', 'icon' => 'bx bx-calendar']
        ]" />

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-calendar font-size-24"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $totalBookings ?? 0 }}</h4>
                                <p class="mb-0">Total Bookings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-check-circle font-size-24"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $confirmedBookings ?? 0 }}</h4>
                                <p class="mb-0">Confirmed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-log-in font-size-24"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $checkedInBookings ?? 0 }}</h4>
                                <p class="mb-0">Checked In</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-time font-size-24"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $pendingBookings ?? 0 }}</h4>
                                <p class="mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title">Booking Management</h4>
                                <p class="card-subtitle text-muted">Manage hotel bookings and reservations</p>
                            </div>
                            <div>
                                <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> New Booking
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="bookingsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Guest Name</th>
                                        <th>Room</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
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
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

/* DataTables custom styling */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
    margin-bottom: 1rem;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.375rem 0.75rem;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.375rem 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Display flash messages using SweetAlert
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#28a745'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'Error!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    @endif

    @if($errors->any())
        Swal.fire({
            title: 'Validation Error!',
            text: '{{ $errors->first() }}',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    @endif

    // Initialize DataTable
    $('#bookingsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('bookings.index') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        },
        columns: [
            {data: 'booking_number', name: 'booking_number'},
            {data: 'guest_name', name: 'guest_name', orderable: false, searchable: false},
            {data: 'room_info', name: 'room_info', orderable: false, searchable: false},
            {data: 'check_in_formatted', name: 'check_in'},
            {data: 'check_out_formatted', name: 'check_out'},
            {data: 'status_badge', name: 'status', orderable: false, searchable: false},
            {data: 'payment_status_badge', name: 'payment_status', orderable: false, searchable: false},
            {data: 'total_amount_formatted', name: 'total_amount'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        order: [[3, 'desc']], // Sort by check-in date descending
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: '<div class="text-center p-4"><i class="bx bx-calendar font-24 text-muted"></i><p class="text-muted mt-2">No bookings found.</p></div>'
        }
    });
});

// SweetAlert confirmation functions for index page
function confirmCheckInIndex(bookingId, actionUrl) {
    console.log('confirmCheckInIndex called for booking:', bookingId);
    
    if (typeof Swal === 'undefined') {
        alert('SweetAlert is not loaded. Please refresh the page.');
        return;
    }
    
    Swal.fire({
        title: 'Check In Guest?',
        text: 'Are you sure you want to check in this guest? This will mark the room as occupied.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Check In',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit the form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmCheckOutIndex(bookingId, actionUrl) {
    console.log('confirmCheckOutIndex called for booking:', bookingId);
    
    if (typeof Swal === 'undefined') {
        alert('SweetAlert is not loaded. Please refresh the page.');
        return;
    }
    
    Swal.fire({
        title: 'Check Out Guest?',
        text: 'Are you sure you want to check out this guest? This will mark the room as available.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Check Out',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit the form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmCancelIndex(bookingId, actionUrl) {
    console.log('confirmCancelIndex called for booking:', bookingId);
    
    if (typeof Swal === 'undefined') {
        alert('SweetAlert is not loaded. Please refresh the page.');
        return;
    }
    
    Swal.fire({
        title: 'Cancel Booking?',
        text: 'Are you sure you want to cancel this booking? This will delete all receipts and payments related to this booking.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Cancel Booking',
        cancelButtonText: 'Keep Booking',
        reverseButtons: true,
        showDenyButton: true,
        denyButtonText: 'Cancel with Fee',
        denyButtonColor: '#fd7e14'
    }).then((result) => {
        if (result.isConfirmed) {
            // Cancel without fee
            submitCancelFormIndex(actionUrl);
        } else if (result.isDenied) {
            // Cancel with fee - show input dialog
            Swal.fire({
                title: 'Cancellation Fee',
                text: 'Enter the cancellation fee amount:',
                input: 'number',
                inputAttributes: {
                    min: 0,
                    step: 0.01,
                    placeholder: '0.00'
                },
                showCancelButton: true,
                confirmButtonText: 'Cancel with Fee',
                cancelButtonText: 'Back',
                inputValidator: (value) => {
                    if (!value || value < 0) {
                        return 'Please enter a valid fee amount (0 or greater)';
                    }
                }
            }).then((feeResult) => {
                if (feeResult.isConfirmed) {
                    submitCancelFormIndex(actionUrl, feeResult.value);
                }
            });
        }
    });
}

function submitCancelFormIndex(actionUrl, fee = 0) {
    // Create and submit the cancel form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = actionUrl;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const feeInput = document.createElement('input');
    feeInput.type = 'hidden';
    feeInput.name = 'cancellation_fee';
    feeInput.value = fee;
    
    const reasonInput = document.createElement('input');
    reasonInput.type = 'hidden';
    reasonInput.name = 'cancellation_reason';
    reasonInput.value = 'Booking cancelled by user';
    
    form.appendChild(csrfToken);
    form.appendChild(feeInput);
    form.appendChild(reasonInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
