@php
    $branchPriceValues = $branchPriceValues ?? [];
    $defaultPrice = old('unit_price', isset($item) ? ($item->default_unit_price ?? '') : '');
@endphp
<div class="row mt-2">
    <div class="col-12">
        <h6 class="text-uppercase mb-2">Branch Selling Prices <span class="text-muted fw-normal">(optional)</span></h6>
        <p class="text-muted small mb-3">
            Leave a branch blank to use the <strong>default selling price</strong> above.
            Each branch sees its own price in item lists and sales.
        </p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Branch</th>
                        <th style="width: 240px;">Branch Selling Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td>{{ $branch->name ?? $branch->branch_name }}</td>
                            <td>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="branch_prices[{{ $branch->id }}]"
                                       class="form-control form-control-sm"
                                       value="{{ old('branch_prices.' . $branch->id, $branchPriceValues[$branch->id] ?? '') }}"
                                       placeholder="{{ $defaultPrice !== '' ? 'Default: ' . number_format((float) $defaultPrice, 2) : 'Use default' }}">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-muted">No branches found for your company.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
