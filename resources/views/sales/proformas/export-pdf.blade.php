<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ $proforma->proforma_number }}</title>
	<style>
		@page { margin: 28px 28px 60px 28px; }
		body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
		.header { margin-bottom: 14px; }
		.row { display: flex; justify-content: space-between; gap: 14px; }
		.col { flex: 1 1 0; }
		.h-left { flex: 1 1 60%; }
		.h-right { flex: 1 1 40%; text-align: right; }
		h1, h2, h3, h4, h5 { margin: 0; padding: 0; }
		.small { font-size: 11px; color: #666; }
		.muted { color: #666; }
		.badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; color: #fff; }
		.badge-draft { background: #6c757d; }
		.badge-sent { background: #0dcaf0; }
		.badge-accepted { background: #198754; }
		.badge-rejected { background: #dc3545; }
		.badge-expired { background: #ffc107; color: #222; }
		.card { border: 1px solid #e5e5e5; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; }
		.card h4 { margin-bottom: 8px; color: #0d6efd; }
		table { width: 100%; border-collapse: collapse; }
		th, td { padding: 8px 10px; border-bottom: 1px solid #eee; }
		th { background: #f8f9fa; text-align: left; font-weight: 600; }
		tr:nth-child(even) td { background: #fcfcfd; }
		.text-right { text-align: right; }
		.text-center { text-align: center; }
		.totals td { border: none; padding: 6px 0; }
		.totals .label { color: #555; }
		.totals .amount { text-align: right; }
		.totals .grand { font-size: 14px; font-weight: 700; }
		.footer { position: fixed; left: 0; right: 0; bottom: 16px; color: #777; font-size: 10px; }
		.footer .row { align-items: center; }
		.divider { height: 1px; background: #eee; margin: 10px 0; }
	</style>
</head>
<body>
	<div class="header">
		<div class="row">
			<div class="h-left">
				<h2>{{ $company->name }}</h2>
				<div class="small">{{ $company->address }}</div>
				<div class="small">Phone: {{ $company->phone }} | Email: {{ $company->email }}</div>
				<div class="small">Branch: {{ $branch ? $branch->name : 'N/A' }}</div>
			</div>
			<div class="h-right">
				<h1>PROFORMA</h1>
				<div class="small">Number: <strong>{{ $proforma->proforma_number }}</strong></div>
				<div class="small">Date: {{ $proforma->proforma_date->format('M d, Y') }}</div>
				<div class="small">Valid Until: {{ $proforma->valid_until->format('M d, Y') }}</div>
				{{-- <div class="small">Status:
					<span class="badge badge-{{ $proforma->status }}">{{ strtoupper($proforma->status) }}</span>
				</div> --}}
			</div>
		</div>
		<div class="divider"></div>
	</div>

	<div class="row">
		<div class="col">
			<div class="card">
				<h4>Customer</h4>
				<div><strong>{{ $proforma->customer->name }}</strong></div>
				<div class="small">{{ $proforma->customer->address }}</div>
				<div class="small">Phone: {{ $proforma->customer->phone }} | Email: {{ $proforma->customer->email }}</div>
			</div>
		</div>
		<div class="col">
			<div class="card">
				<h4>Sales Info</h4>
				<div class="small">Created By: {{ optional($proforma->createdBy)->name }}</div>
				<div class="small">Company: {{ $company->name }}</div>
				<div class="small">Branch: {{ $branch ? $branch->name : 'N/A' }}</div>
			</div>
		</div>
	</div>

	<div class="card" style="padding:0;">
		<table>
			<thead>
				<tr>
					<th style="width:42%">Item</th>
					<th class="text-right" style="width:10%">Qty</th>
					<th class="text-right" style="width:14%">Unit Price</th>
					<th class="text-right" style="width:12%">Discount</th>
					<th class="text-right" style="width:10%">VAT</th>
					<th class="text-right" style="width:12%">Line Total</th>
				</tr>
			</thead>
			<tbody>
				@foreach($proforma->items as $item)
				<tr>
					<td>
						<strong>{{ $item->item_name }}</strong>
						@if($item->item_code)
							<span class="small muted">({{ $item->item_code }})</span>
						@endif
					</td>
					<td class="text-right">{{ number_format($item->quantity, 2) }}</td>
					<td class="text-right">TZS {{ number_format($item->unit_price, 2) }}</td>
					<td class="text-right">{{ $item->discount_amount > 0 ? 'TZS ' . number_format($item->discount_amount, 2) : '-' }}</td>
					<td class="text-right">{{ $item->vat_amount > 0 ? 'TZS ' . number_format($item->vat_amount, 2) : '-' }}</td>
					<td class="text-right">TZS {{ number_format($item->line_total, 2) }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="row" style="margin-top:8px;">
		<div class="col">
			@if($proforma->notes)
				<div class="card">
					<h4>Notes</h4>
					<div>{{ $proforma->notes }}</div>
				</div>
			@endif
			@if($proforma->terms_conditions)
				<div class="card">
					<h4>Terms & Conditions</h4>
					<div>{{ $proforma->terms_conditions }}</div>
				</div>
			@endif
		</div>
		<div class="col">
			<div class="card">
				<h4>Summary</h4>
				@php
					// Calculate VAT from items
					$calculatedVatAmount = 0;
					foreach($proforma->items as $item) {
						$calculatedVatAmount += $item->vat_amount;
					}
				@endphp
				<table class="totals" style="width:100%">
					<tr>
						<td class="label">Subtotal (without VAT)</td>
						<td class="amount">TZS {{ number_format($proforma->subtotal - $calculatedVatAmount, 2) }}</td>
					</tr>
					<tr>
						<td class="label">VAT</td>
						<td class="amount">TZS {{ number_format($proforma->vat_amount, 2) }}</td>
					</tr>
					<tr>
						<td class="label">Additional Tax</td>
						<td class="amount">TZS {{ number_format($proforma->tax_amount, 2) }}</td>
					</tr>
					<tr>
						<td class="label">Discount</td>
						<td class="amount">-TZS {{ number_format($proforma->discount_amount, 2) }}</td>
					</tr>
					<tr>
						<td class="label grand">Grand Total</td>
						<td class="amount grand">TZS {{ number_format($proforma->total_amount, 2) }}</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<div class="footer">
		<div class="row">
			<div class="col">Generated on {{ now()->format('M d, Y h:i A') }}</div>
			<div class="col text-right">Page <span class="page-number"></span></div>
		</div>
	</div>
	<script type="text/php">
		if (isset($pdf)) { $x=520; $y=820; $text = "Page {PAGE_NUM} of {PAGE_COUNT}"; $font = $fontMetrics->get_font("DejaVu Sans", "normal"); $size = 9; $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0)); }
	</script>
</body>
</html> 