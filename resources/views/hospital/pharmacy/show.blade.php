@extends('layouts.main')

@section('title', 'Dispensation Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Pharmacy', 'url' => route('hospital.pharmacy.index'), 'icon' => 'bx bx-capsule'],
                ['label' => 'Dispensation Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">DISPENSATION DETAILS</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Patient Info -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                            <div>
                                @if($dispensation->status === 'pending')
                                    <form action="{{ route('hospital.pharmacy.dispense', $dispensation->id) }}" method="POST" class="d-inline" id="dispenseForm">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to dispense these medications? This will update stock levels.')">
                                            <i class="bx bx-check me-1"></i>Dispense Medications
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.pharmacy.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Patient:</th>
                                            <td><strong>{{ $dispensation->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $dispensation->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $dispensation->patient->age ? $dispensation->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $dispensation->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Dispensation #:</th>
                                            <td><strong>{{ $dispensation->dispensation_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'dispensed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $statusColor = $statusColors[$dispensation->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst($dispensation->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($dispensation->bill)
                                            <tr>
                                                <th>Bill:</th>
                                                <td>
                                                    <a href="{{ route('hospital.cashier.bills.show', $dispensation->bill->id) }}">
                                                        {{ $dispensation->bill->bill_number }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                        @if($dispensation->dispensed_at)
                                            <tr>
                                                <th>Dispensed At:</th>
                                                <td>{{ $dispensation->dispensed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dispensed By:</th>
                                                <td>{{ $dispensation->dispensedBy->name ?? 'N/A' }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-capsule me-2"></i>Medications
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($itemsWithStock->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Medication</th>
                                                <th>Prescribed</th>
                                                <th>Dispensed</th>
                                                <th>Available Stock</th>
                                                <th>Dosage Instructions</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($itemsWithStock as $item)
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'dispensed' => 'success',
                                                        'partial' => 'info',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $statusColor = $statusColors[$item->status] ?? 'secondary';
                                                    $stockClass = $item->available_stock >= $item->quantity_dispensed ? 'text-success' : 'text-danger';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                                        @if($item->product)
                                                            <br><small class="text-muted">{{ $item->product->code }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->quantity_prescribed }}</td>
                                                    <td>
                                                        <strong>{{ $item->quantity_dispensed }}</strong>
                                                    </td>
                                                    <td>
                                                        @if($item->product && $item->product->track_stock)
                                                            <span class="{{ $stockClass }}">
                                                                {{ $item->available_stock }} {{ $item->product->unit_of_measure ?? '' }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->dosage_instructions ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $statusColor }}">
                                                            {{ ucfirst($item->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No medications in this dispensation.</p>
                            @endif

                            @if($dispensation->instructions)
                                <div class="mt-3">
                                    <h6>Additional Instructions:</h6>
                                    <p class="mb-0">{{ $dispensation->instructions }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
