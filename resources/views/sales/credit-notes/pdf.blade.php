<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credit Note {{ $creditNote->credit_note_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header, .footer { width: 100%; }
        .header td { vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        .table { border: 1px solid #ddd; }
        .table th, .table td { border: 1px solid #ddd; padding: 4px; }
        .table thead th { background: #f2f2f2; font-weight: bold; }
        .table-borderless th, .table-borderless td { border: none; padding: 2px 0; }
        .small { font-size: 10px; color: #666; }
        .smaller { font-size: 9px; color: #888; }
        .fw-bold { font-weight: bold; }
        .section-title { font-size: 12px; font-weight: bold; margin: 12px 0 6px 0; color: #333; }
        .status-badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .status-draft { background: #6c757d; color: white; }
        .status-issued { background: #ffc107; color: black; }
        .status-applied { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .type-badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .type-return { background: #007bff; color: white; }
        .type-exchange { background: #17a2b8; color: white; }
        .type-discount { background: #28a745; color: white; }
        .type-correction { background: #ffc107; color: black; }
        .type-other { background: #6c757d; color: white; }
        .summary-box { border: 1px solid #ddd; padding: 8px; background: #f8f9fa; }
        .page-break { page-break-before: always; }
    </style>
    </head>
<body>
    <!-- Header Section -->
    <table class="header">
        <tr>
            <td style="width:60%;">
                @if($company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Company Logo" style="max-height: 60px; margin-bottom: 8px;">
                @endif
                <h2 style="margin:0; font-size: 18px;">{{ $company->name ?? 'Company' }}</h2>
                <div class="small">
                    {{ $company->address ?? '' }}<br>
                    {{ $company->email ?? '' }} {{ $company->phone ? ' | ' . $company->phone : '' }}
                </div>
            </td>
            <td style="width:40%;" class="text-right">
                <h2 style="margin:0; font-size: 18px; color: #007bff;">Credit Note</h2>
                <div class="small">
                    <strong>No:</strong> {{ $creditNote->credit_note_number }}<br>
                    <strong>Date:</strong> {{ $creditNote->credit_note_date->format('M d, Y') }}<br>
                    <strong>Status:</strong> 
                    <span class="status-badge status-{{ $creditNote->status }}">{{ strtoupper($creditNote->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Credit Note Information -->
    <div class="section-title">Credit Note Information</div>
    <table class="table-borderless" style="width: 100%;">
        <tr>
            <td style="width:50%; vertical-align: top;">
                <table class="table-borderless">
                    <tr>
                        <td style="width:40%;" class="fw-bold">Credit Note Number:</td>
                        <td>{{ $creditNote->credit_note_number }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Date:</td>
                        <td>{{ $creditNote->credit_note_date->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Type:</td>
                        <td><span class="type-badge type-{{ $creditNote->type }}">{{ strtoupper(str_replace('_', ' ', $creditNote->type)) }}</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">VAT Rate:</td>
                        <td>{{ $creditNote->vat_rate }}%</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Currency:</td>
                        <td>{{ $creditNote->currency }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:50%; vertical-align: top;">
                <table class="table-borderless">
                    <tr>
                        <td style="width:40%;" class="fw-bold">Status:</td>
                        <td><span class="status-badge status-{{ $creditNote->status }}">{{ strtoupper($creditNote->status) }}</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Created By:</td>
                        <td>{{ $creditNote->createdBy->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Created Date:</td>
                        <td>{{ $creditNote->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @if($creditNote->approvedBy)
                    <tr>
                        <td class="fw-bold">Approved By:</td>
                        <td>{{ $creditNote->approvedBy->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Approved Date:</td>
                        <td>{{ $creditNote->approved_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Customer Information -->
    <div class="section-title">Customer Information</div>
    <table class="table-borderless" style="width: 100%;">
        <tr>
            <td style="width:50%; vertical-align: top;">
                <table class="table-borderless">
                    <tr>
                        <td style="width:40%;" class="fw-bold">Customer Name:</td>
                        <td>{{ $creditNote->customer->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Phone:</td>
                        <td>{{ $creditNote->customer->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email:</td>
                        <td>{{ $creditNote->customer->email ?? 'N/A' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Related Invoice Information -->
    @if($creditNote->salesInvoice || $creditNote->referenceInvoice)
    <div class="section-title">Related Invoice Information</div>
    <table class="table-borderless" style="width: 100%;">
        @if($creditNote->salesInvoice)
        <tr>
            <td style="width:50%; vertical-align: top;">
                <table class="table-borderless">
                    <tr>
                        <td style="width:40%;" class="fw-bold">Invoice Number:</td>
                        <td>{{ $creditNote->salesInvoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Invoice Date:</td>
                        <td>{{ $creditNote->salesInvoice->invoice_date->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Invoice Amount:</td>
                        <td>TZS {{ number_format($creditNote->salesInvoice->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:50%; vertical-align: top;">
                <table class="table-borderless">
                    <tr>
                        <td style="width:40%;" class="fw-bold">Invoice Status:</td>
                        <td>{{ strtoupper($creditNote->salesInvoice->status) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Due Date:</td>
                        <td>{{ $creditNote->salesInvoice->due_date ? $creditNote->salesInvoice->due_date->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Balance:</td>
                        <td>TZS {{ number_format($creditNote->salesInvoice->balance_due, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
        @if($creditNote->referenceInvoice && (!$creditNote->salesInvoice || $creditNote->referenceInvoice->id !== $creditNote->salesInvoice->id))
        <tr>
            <td colspan="2" style="padding-top: 8px;">
                <div class="smaller fw-bold">Reference Invoice:</div>
                <div class="small">{{ $creditNote->referenceInvoice->invoice_number }} - {{ $creditNote->referenceInvoice->invoice_date->format('M d, Y') }} - TZS {{ number_format($creditNote->referenceInvoice->total_amount, 2) }}</div>
            </td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Exchange Information -->
    @if($creditNote->type === 'exchange')
    <div class="section-title">Exchange Information</div>
    <table style="width: 100%; border: 1px solid #ddd;">
        <tr>
            <td style="width:50%; padding: 8px; text-align: center; border-right: 1px solid #ddd;">
                <div class="fw-bold" style="color: #ffc107;">Returned Items</div>
                <div class="small">{{ $creditNote->items->where('is_replacement', false)->count() }} items returned</div>
            </td>
            <td style="width:50%; padding: 8px; text-align: center;">
                <div class="fw-bold" style="color: #28a745;">Replacement Items</div>
                <div class="small">{{ $creditNote->items->where('is_replacement', true)->count() }} items provided</div>
            </td>
        </tr>
    </table>
    @if($creditNote->refund_now)
    <div class="small mt-2" style="background: #d1ecf1; padding: 6px; border-radius: 3px;">
        <strong>Refund Processing:</strong> Customer will receive a refund for the returned items.
        @if($creditNote->bankAccount)
        <br>Refund will be processed from: {{ $creditNote->bankAccount->name }} ({{ $creditNote->bankAccount->account_number }})
        @endif
    </div>
    @endif
    @if($creditNote->return_to_stock)
    <div class="small mt-2" style="background: #d4edda; padding: 6px; border-radius: 3px;">
        <strong>Return to Stock:</strong> Returned items will be added back to inventory.
    </div>
    @endif
    @endif

    <!-- Reason and Notes -->
    @if($creditNote->reason)
    <div class="section-title">Reason</div>
    <div class="small" style="background: #f8f9fa; padding: 8px; border-radius: 3px;">{{ $creditNote->reason }}</div>
    @endif

    @if($creditNote->notes)
    <div class="section-title">Notes</div>
    <div class="small" style="background: #f8f9fa; padding: 8px; border-radius: 3px;">{{ $creditNote->notes }}</div>
    @endif

    <!-- Credit Note Items -->
    <div class="section-title">Credit Note Items</div>
    <table class="table mt-2">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:35%">Item Name</th>
                <th style="width:8%">Type</th>
                <th style="width:8%" class="text-right">Qty</th>
                <th style="width:12%" class="text-right">Unit Price</th>
                <th style="width:12%" class="text-right">VAT</th>
                <th style="width:10%" class="text-right">Discount</th>
                <th style="width:10%" class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($creditNote->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <div class="fw-bold">{{ $item->item_name }}</div>
                    @if($item->item_code)
                        <div class="smaller">Code: {{ $item->item_code }}</div>
                    @endif
                    @if($item->description)
                        <div class="smaller">{{ $item->description }}</div>
                    @endif
                </td>
                <td>
                    @if($creditNote->type === 'exchange')
                        @if($item->is_replacement)
                            <span class="smaller" style="color: #28a745;">Replacement</span>
                        @else
                            <span class="smaller" style="color: #ffc107;">Returned</span>
                        @endif
                    @else
                        <span class="smaller" style="color: #007bff;">Credit</span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">TZS {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">
                    @if($item->vat_type == 'no_vat')
                        <div class="smaller">No VAT</div>
                    @elseif($item->vat_type == 'inclusive')
                        <div class="smaller">Inc {{ $item->vat_rate }}%</div>
                    @else
                        <div class="smaller">Exc {{ $item->vat_rate }}%</div>
                    @endif
                    <div class="smaller">TZS {{ number_format($item->vat_amount, 2) }}</div>
                </td>
                <td class="text-right">
                    @if($item->discount_type !== 'none')
                        <div class="smaller">{{ ucfirst($item->discount_type) }} {{ $item->discount_rate }}%</div>
                        <div class="smaller">TZS {{ number_format($item->discount_amount, 2) }}</div>
                    @else
                        <div class="smaller">None</div>
                    @endif
                </td>
                <td class="text-right fw-bold">TZS {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">
                    <div class="small">No items found</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Financial Summary -->
    <div class="section-title">Financial Summary</div>
    <table class="mt-2">
        <tr>
            <td style="width:60%"></td>
            <td style="width:40%">
                <div class="summary-box">
                    <table style="width:100%">
                        <tr>
                            <td class="text-right fw-bold">Subtotal:</td>
                            <td class="text-right fw-bold" style="width:120px;">TZS {{ number_format($creditNote->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-right fw-bold">VAT Amount:</td>
                            <td class="text-right fw-bold">TZS {{ number_format($creditNote->vat_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-right fw-bold">Discount:</td>
                            <td class="text-right fw-bold">TZS {{ number_format($creditNote->discount_amount, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1px solid #ddd; margin-top: 4px; padding-top: 4px;">
                            <td class="text-right fw-bold" style="font-size: 13px;">Total Amount:</td>
                            <td class="text-right fw-bold" style="font-size: 13px; color: #007bff;">TZS {{ number_format($creditNote->total_amount, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1px solid #ddd; margin-top: 4px; padding-top: 4px;">
                            <td class="text-right fw-bold">Applied Amount:</td>
                            <td class="text-right fw-bold" style="color: #28a745;">TZS {{ number_format($creditNote->applied_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-right fw-bold">Remaining Amount:</td>
                            <td class="text-right fw-bold" style="color: #ffc107;">TZS {{ number_format($creditNote->remaining_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

  

    <!-- Footer -->
    <div class="footer mt-4" style="border-top: 1px solid #ddd; padding-top: 8px;">
        <table style="width: 100%;">
            <tr>
                <td class="small">
                    <strong>Generated on:</strong> {{ now()->format('M d, Y H:i') }}<br>
                    <strong>Generated by:</strong> {{ auth()->user()->name ?? 'System' }}
                </td>
                <td class="text-right small">
                    <strong>Company:</strong> {{ $company->name ?? 'N/A' }}<br>
                    <strong>Branch:</strong> {{ $creditNote->branch->name ?? 'N/A' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>


