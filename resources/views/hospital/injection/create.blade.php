@extends('layouts.main')

@section('title', 'Record Injection')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Injection', 'url' => route('hospital.injection.index'), 'icon' => 'bx bx-injection'],
                ['label' => 'Record Injection', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD INJECTION</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bx bx-user me-2"></i>Patient: {{ $visit->patient->full_name }} (MRN: {{ $visit->patient->mrn }})</h5>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Visit Status -->
                            <div class="alert alert-danger mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Visit #:</strong> {{ $visit->visit_number }}<br>
                                        <strong>Visit Status:</strong> 
                                        @if($vaccinationDept)
                                            @if($vaccinationDept->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($vaccinationDept->status === 'in_service')
                                                <span class="badge bg-primary">In Service</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Not Started</span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Patient:</strong> {{ $visit->patient->full_name }}<br>
                                        <strong>MRN:</strong> {{ $visit->patient->mrn }}<br>
                                        <strong>Age:</strong> {{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            @if($injectionInvoice && $injectionInvoiceItems->count() > 0)
                                <div class="alert alert-info mb-4">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Injection Items from Doctor:</strong> The following services/products were ordered by the doctor. Please add records for each item and mark as completed.
                                </div>

                                <form action="{{ route('hospital.injection.store', $visit->id) }}" method="POST" id="injection-records-form">
                                    @csrf

                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="18%">Item Name</th>
                                                    <th width="12%">Type</th>
                                                    <th width="12%">Description</th>
                                                    <th width="8%">Quantity</th>
                                                    <th width="10%">Unit Price</th>
                                                    <th width="15%">Injection Type</th>
                                                    <th width="10%">Status</th>
                                                    <th width="10%">Item Status</th>
                                                    <th width="5%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($injectionInvoiceItems as $index => $item)
                                                    @php
                                                        $itemId = $item->inventory_item_id;
                                                        $inventoryItem = $item->inventoryItem ?? null;
                                                        $existingRecord = $existingRecords->get($itemId);
                                                        $itemType = $inventoryItem ? ($inventoryItem->item_type === 'service' ? 'Service' : 'Product') : 'N/A';
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="records[{{ $index }}][item_id]" value="{{ $itemId }}">
                                                            <input type="hidden" name="records[{{ $index }}][item_name]" value="{{ $item->item_name }}">
                                                            <strong>{{ $item->item_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $inventoryItem->code ?? $item->item_code ?? 'N/A' }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $itemType === 'Service' ? 'primary' : 'success' }}">{{ $itemType }}</span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $item->description ?? 'No description' }}</small>
                                                        </td>
                                                        <td>{{ number_format($item->quantity, 2) }}</td>
                                                        <td>{{ number_format($item->unit_price, 2) }} TZS</td>
                                                        <td>
                                                            @if($existingRecord)
                                                                <input type="hidden" name="records[{{ $index }}][record_id]" value="{{ $existingRecord->id }}">
                                                                <select class="form-select form-select-sm" name="records[{{ $index }}][injection_type]">
                                                                    <option value="Intramuscular" {{ ($existingRecord->injection_type ?? old("records.{$index}.injection_type")) == 'Intramuscular' ? 'selected' : '' }}>Intramuscular</option>
                                                                    <option value="Intravenous" {{ ($existingRecord->injection_type ?? old("records.{$index}.injection_type")) == 'Intravenous' ? 'selected' : '' }}>Intravenous</option>
                                                                    <option value="Subcutaneous" {{ ($existingRecord->injection_type ?? old("records.{$index}.injection_type")) == 'Subcutaneous' ? 'selected' : '' }}>Subcutaneous</option>
                                                                    <option value="Intradermal" {{ ($existingRecord->injection_type ?? old("records.{$index}.injection_type")) == 'Intradermal' ? 'selected' : '' }}>Intradermal</option>
                                                                    <option value="Other" {{ ($existingRecord->injection_type ?? old("records.{$index}.injection_type")) == 'Other' ? 'selected' : '' }}>Other</option>
                                                                </select>
                                                            @else
                                                                <select class="form-select form-select-sm" name="records[{{ $index }}][injection_type]">
                                                                    <option value="">Select...</option>
                                                                    <option value="Intramuscular" {{ old("records.{$index}.injection_type") == 'Intramuscular' ? 'selected' : '' }}>Intramuscular</option>
                                                                    <option value="Intravenous" {{ old("records.{$index}.injection_type") == 'Intravenous' ? 'selected' : '' }}>Intravenous</option>
                                                                    <option value="Subcutaneous" {{ old("records.{$index}.injection_type") == 'Subcutaneous' ? 'selected' : '' }}>Subcutaneous</option>
                                                                    <option value="Intradermal" {{ old("records.{$index}.injection_type") == 'Intradermal' ? 'selected' : '' }}>Intradermal</option>
                                                                    <option value="Other" {{ old("records.{$index}.injection_type") == 'Other' ? 'selected' : '' }}>Other</option>
                                                                </select>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($existingRecord)
                                                                <select class="form-select form-select-sm" name="records[{{ $index }}][status]">
                                                                    <option value="pending" {{ ($existingRecord->status ?? old("records.{$index}.status", 'pending')) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                    <option value="completed" {{ ($existingRecord->status ?? old("records.{$index}.status")) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                                    <option value="follow_up_required" {{ ($existingRecord->status ?? old("records.{$index}.status")) == 'follow_up_required' ? 'selected' : '' }}>Follow-up Required</option>
                                                                </select>
                                                            @else
                                                                <select class="form-select form-select-sm" name="records[{{ $index }}][status]">
                                                                    <option value="pending" {{ old("records.{$index}.status", 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                    <option value="completed" {{ old("records.{$index}.status") == 'completed' ? 'selected' : '' }}>Completed</option>
                                                                    <option value="follow_up_required" {{ old("records.{$index}.status") == 'follow_up_required' ? 'selected' : '' }}>Follow-up Required</option>
                                                                </select>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($existingRecord)
                                                                @if($existingRecord->status === 'completed')
                                                                    <span class="badge bg-success">Completed</span>
                                                                @elseif($existingRecord->status === 'follow_up_required')
                                                                    <span class="badge bg-warning">Follow-up</span>
                                                                @else
                                                                    <span class="badge bg-info">Pending</span>
                                                                @endif
                                                            @else
                                                                <span class="badge bg-secondary">Not Started</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($existingRecord)
                                                                <a href="{{ route('hospital.injection.show', $existingRecord->id) }}" class="btn btn-sm btn-danger" target="_blank">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Note:</strong> Select injection type and status for each item. Mark items as "Completed" when injections are finished. Once all items are completed, the visit will be marked as completed.
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="{{ route('hospital.injection.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bx bx-save me-1"></i>Save Records
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>No Injection Items Found:</strong> No injection services/products have been ordered by the doctor for this visit. Please contact the doctor to create an injection bill first.
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('hospital.injection.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i>Back to Injection Dashboard
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
