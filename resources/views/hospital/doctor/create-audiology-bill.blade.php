@extends('layouts.main')

@section('title', 'Create Audiology Test Bill')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Doctor', 'url' => route('hospital.doctor.index'), 'icon' => 'bx bx-user-md'],
                ['label' => 'Audiology Test Bill', 'url' => '#', 'icon' => 'bx bx-volume-full']
            ]" />
            <h6 class="mb-0 text-uppercase">CREATE AUDIOLOGY TEST BILL</h6>
            <hr />

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-user me-2"></i>Patient & Visit</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Patient:</strong> {{ $visit->patient->full_name ?? 'N/A' }}</p>
                            <p><strong>MRN:</strong> {{ $visit->patient->mrn ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Visit #:</strong> {{ $visit->visit_number }}</p>
                            <p><strong>Visit Date:</strong> {{ $visit->visit_date ? $visit->visit_date->format('d M Y, H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bx bx-volume-full me-2"></i>Select Audiology Services</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hospital.doctor.store-audiology-bill', $visit->id) }}" method="POST">
                        @csrf

                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Select audiology services to bill. Patient will be sent to Cashier for payment and then to Audiology.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th>Service</th>
                                        <th style="width: 120px;">Qty</th>
                                        <th style="width: 150px;">Unit Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($services as $service)
                                        <tr>
                                            <td>
                                                <input class="form-check-input audiology-service-checkbox" type="checkbox" name="items[{{ $loop->index }}][inventory_item_id]" value="{{ $service->id }}">
                                            </td>
                                            <td>
                                                <strong>{{ $service->name }}</strong>
                                                @if($service->code)
                                                    <br><small class="text-muted">{{ $service->code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm audiology-qty" name="items[{{ $loop->index }}][quantity]" value="1" min="1" disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm audiology-price" name="items[{{ $loop->index }}][unit_price]" value="{{ $service->unit_price ?? 0 }}" min="0" step="0.01" disabled>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                No services found. Please create inventory items with item_type = 'service'.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('hospital.doctor.create', $visit->id) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-dark">
                                <i class="bx bx-save me-1"></i>Create Bill & Send to Cashier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.audiology-service-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', function () {
                    const row = this.closest('tr');
                    const qty = row.querySelector('.audiology-qty');
                    const price = row.querySelector('.audiology-price');
                    if (qty) qty.disabled = !this.checked;
                    if (price) price.disabled = !this.checked;
                });
            });
        });
    </script>
@endsection

