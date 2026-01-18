@extends('layouts.main')

@section('title', 'Create Vaccination Bill')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
            ['label' => 'Doctor', 'url' => route('hospital.doctor.index'), 'icon' => 'bx bx-user-md'],
            ['label' => 'Create Vaccination Bill', 'url' => '#', 'icon' => 'bx bx-shield']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE VACCINATION BILL</h6>
        <hr />

        <div class="card">
            <div class="card-header bg-warning text-white">
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

                <form id="vaccination-bill-form" method="POST" action="{{ route('hospital.doctor.store-vaccination-bill', $visit->id) }}">
                    @csrf

                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Note:</strong> Select services and products (vaccines) for this patient. After creating the bill, the patient will be sent to the cashier for payment. Once paid, the patient can proceed to vaccination department.
                    </div>

                    <!-- Items Section -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Services & Products / Huduma & Bidhaa</h6>
                                <button type="button" class="btn btn-warning btn-sm" id="add-item" data-bs-toggle="modal" data-bs-target="#itemModal">
                                    <i class="bx bx-plus me-1"></i>Add Item
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="items-table">
                                    <thead>
                                        <tr>
                                            <th width="20%">Item</th>
                                            <th width="20%">Description</th>
                                            <th width="12%">Quantity</th>
                                            <th width="12%">Unit Price</th>
                                            <th width="10%">VAT</th>
                                            <th width="12%">Total</th>
                                            <th width="10%">Action</th>
                                            <th width="4%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        <!-- Items will be added here dynamically -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                            <td><strong id="subtotal">0.00</strong></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <input type="hidden" name="subtotal" id="subtotal-input" value="0">
                                        <tr id="vat-row" style="display: none;">
                                            <td colspan="5" class="text-end"><strong>VAT Amount:</strong></td>
                                            <td><strong id="vat-amount">0.00</strong></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <input type="hidden" name="vat_amount" id="vat-amount-input" value="0">
                                        <tr>
                                            <td colspan="5" class="text-end"><strong>Discount:</strong></td>
                                            <td>
                                                <input type="number" class="form-control" id="discount_amount" name="discount_amount" 
                                                       value="0" step="0.01" min="0" placeholder="0.00">
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td colspan="5" class="text-end"><strong>Total Amount:</strong></td>
                                            <td><strong id="total-amount">0.00</strong></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <input type="hidden" name="total_amount" id="total-amount-input" value="0">
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('hospital.doctor.create', $visit->id) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-warning" id="submit-btn">
                            <i class="bx bx-check me-1"></i>Create Bill & Send to Cashier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Item Selection Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item (Service or Product)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="modal_item_id" class="form-label">Select Item</label>
                    <select class="form-select select2-modal" id="modal_item_id">
                        <option value="">Choose an item...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" 
                                    data-name="{{ $item->name }}"
                                    data-code="{{ $item->code }}"
                                    data-price="{{ $item->unit_price }}"
                                    data-unit="{{ $item->unit_of_measure }}"
                                    data-type="{{ $item->item_type }}"
                                    data-vat-rate="0"
                                    data-vat-type="no_vat">
                                [{{ strtoupper($item->item_type) }}] {{ $item->name }} ({{ $item->code }}) - Price: {{ number_format($item->unit_price, 2) }} TZS
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="modal_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="modal_quantity" value="1" step="0.01" min="0.01">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="modal_unit_price" class="form-label">Unit Price</label>
                            <input type="number" class="form-control" id="modal_unit_price" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="modal_description" class="form-label">Description <span class="text-muted">(Optional)</span></label>
                    <textarea class="form-control" id="modal_description" rows="3" placeholder="Enter any additional notes or instructions..."></textarea>
                    <small class="text-muted">Enter any additional notes or instructions for this item.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Line Total</label>
                    <div class="border rounded p-2 bg-light">
                        <span class="fw-bold" id="modal-line-total">0.00</span> TZS
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="add-item-btn">Add Item</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.select2-modal {
    width: 100% !important;
}
.item-description-text {
    font-size: 0.9rem;
    white-space: pre-wrap;
    word-wrap: break-word;
    max-height: 100px;
    overflow-y: auto;
}
</style>
@endpush

@push('scripts')
<script>
let itemCounter = 0;

$(document).ready(function() {
    // Initialize Select2 for item selection
    $('.select2-modal').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#itemModal')
    });

    // Update modal line total when item, quantity, or price changes
    $('#modal_item_id, #modal_quantity, #modal_unit_price').on('change', function() {
        updateModalLineTotal();
    });

    // Auto-fill unit price when item is selected
    $('#modal_item_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const price = parseFloat(selectedOption.data('price')) || 0;
            $('#modal_unit_price').val(price.toFixed(2));
            updateModalLineTotal();
        }
    });

    // Add item button click
    $('#add-item-btn').on('click', function() {
        addItemToTable();
    });

    // Remove item from table
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    // Update totals when quantity or price changes
    $(document).on('change', '.item-quantity, .item-price', function() {
        updateRowTotal($(this).closest('tr'));
        calculateTotals();
    });
});

function updateModalLineTotal() {
    const quantity = parseFloat($('#modal_quantity').val()) || 0;
    const unitPrice = parseFloat($('#modal_unit_price').val()) || 0;
    const lineTotal = quantity * unitPrice;
    $('#modal-line-total').text(lineTotal.toFixed(2));
}

function addItemToTable() {
    const itemId = $('#modal_item_id').val();
    const selectedOption = $('#modal_item_id option:selected');

    if (!itemId) {
        Swal.fire('Error', 'Please select an item', 'error');
        return;
    }

    const itemName = selectedOption.data('name');
    const itemCode = selectedOption.data('code');
    const itemType = selectedOption.data('type');
    const quantity = parseFloat($('#modal_quantity').val()) || 1;
    const unitPrice = parseFloat($('#modal_unit_price').val()) || parseFloat(selectedOption.data('price')) || 0;
    const description = $('#modal_description').val() || '';
    const vatType = selectedOption.data('vat-type') || 'no_vat';
    const vatRate = parseFloat(selectedOption.data('vat-rate')) || 0;

    // Check if item already exists in table
    const existingRow = $(`#items-tbody tr[data-item-id="${itemId}"]`);
    if (existingRow.length > 0) {
        const currentQuantity = parseFloat(existingRow.find('.item-quantity').val()) || 0;
        const newQuantity = currentQuantity + quantity;
        existingRow.find('.item-quantity').val(newQuantity);
        updateRowTotal(existingRow);
        calculateTotals();
        $('#itemModal').modal('hide');
        resetModal();
        return;
    }

    const lineTotal = quantity * unitPrice;

    itemCounter++;

    // Escape HTML for safe display
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };
    
    const escapedDescription = escapeHtml(description);
    const descriptionDisplay = description ? escapedDescription.replace(/\n/g, '<br>') : '<span class="text-muted">No description</span>';

    const typeBadge = itemType === 'service' ? '<span class="badge bg-info">Service</span>' : '<span class="badge bg-success">Product</span>';

    const row = `
        <tr data-item-id="${itemId}">
            <td>
                <input type="hidden" name="items[${itemCounter}][inventory_item_id]" value="${itemId}">
                <input type="hidden" name="items[${itemCounter}][vat_type]" value="${vatType}">
                <input type="hidden" name="items[${itemCounter}][vat_rate]" value="${vatRate}">
                <input type="hidden" name="items[${itemCounter}][vat_amount]" value="0">
                <input type="hidden" name="items[${itemCounter}][discount_type]" value="percentage">
                <input type="hidden" name="items[${itemCounter}][discount_rate]" value="0">
                <input type="hidden" name="items[${itemCounter}][discount_amount]" value="0">
                <input type="hidden" name="items[${itemCounter}][line_total]" value="${lineTotal}">
                <div class="fw-bold">${itemName}</div>
                <small class="text-muted">${itemCode}</small>
                <div>${typeBadge}</div>
            </td>
            <td>
                <input type="hidden" name="items[${itemCounter}][description]" value="${escapeHtml(description)}">
                <div class="item-description-text">${descriptionDisplay}</div>
            </td>
            <td>
                <input type="number" class="form-control item-quantity" 
                       name="items[${itemCounter}][quantity]" value="${quantity}" 
                       step="0.01" min="0.01" data-row="${itemCounter}">
            </td>
            <td>
                <input type="number" class="form-control item-price" 
                       name="items[${itemCounter}][unit_price]" value="${unitPrice.toFixed(2)}" 
                       step="0.01" min="0" data-row="${itemCounter}">
            </td>
            <td>
                <small class="text-muted">No VAT</small>
            </td>
            <td>
                <span class="line-total">${lineTotal.toFixed(2)}</span>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
            <td></td>
        </tr>
    `;

    $('#items-tbody').append(row);
    calculateTotals();
    $('#itemModal').modal('hide');
    resetModal();
}

function updateRowTotal(row) {
    const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
    const unitPrice = parseFloat(row.find('.item-price').val()) || 0;
    const lineTotal = quantity * unitPrice;

    row.find('.line-total').text(lineTotal.toFixed(2));
    row.find('input[name*="[line_total]"]').val(lineTotal);
}

function calculateTotals() {
    let subtotal = 0;

    $('#items-tbody tr').each(function() {
        const lineTotal = parseFloat($(this).find('input[name*="[line_total]"]').val()) || 0;
        subtotal += lineTotal;
    });

    const discount = parseFloat($('#discount_amount').val()) || 0;
    const total = subtotal - discount;

    $('#subtotal').text(subtotal.toFixed(2));
    $('#subtotal-input').val(subtotal);
    $('#total-amount').text(total.toFixed(2));
    $('#total-amount-input').val(total);
}

function resetModal() {
    $('#modal_item_id').val('').trigger('change');
    $('#modal_quantity').val(1);
    $('#modal_unit_price').val('');
    $('#modal_description').val('');
    $('#modal-line-total').text('0.00');
}

// Update totals when discount changes
$('#discount_amount').on('change', function() {
    calculateTotals();
});
</script>
@endpush
@endsection
