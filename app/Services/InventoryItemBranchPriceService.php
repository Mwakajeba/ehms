<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemBranchPrice;

class InventoryItemBranchPriceService
{
    /**
     * Sync optional per-branch selling price overrides for an item.
     *
     * @param  array<int, mixed>  $branchPrices  branch_id => price (empty removes override)
     */
    public function sync(Item $item, array $branchPrices, int $companyId): void
    {
        $branchIds = Branch::where('company_id', $companyId)->pluck('id');

        foreach ($branchIds as $branchId) {
            $branchId = (int) $branchId;
            $price = $branchPrices[$branchId] ?? $branchPrices[(string) $branchId] ?? null;

            if ($price === '' || $price === null) {
                ItemBranchPrice::where('item_id', $item->id)
                    ->where('branch_id', $branchId)
                    ->delete();
                continue;
            }

            ItemBranchPrice::updateOrCreate(
                [
                    'item_id' => $item->id,
                    'branch_id' => $branchId,
                ],
                [
                    'unit_price' => $price,
                ]
            );
        }
    }
}
