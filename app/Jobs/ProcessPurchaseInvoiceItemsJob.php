<?php

namespace App\Jobs;

use App\Models\Purchase\PurchaseInvoice;
use App\Models\Purchase\PurchaseInvoiceItem;
use App\Models\Assets\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPurchaseInvoiceItemsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $invoiceId,
        private array $itemsData,
        private float $discountAmount = 0,
        private float $withholdingTaxAmount = 0
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('ProcessPurchaseInvoiceItemsJob: Starting', [
            'invoice_id' => $this->invoiceId,
            'items_count' => count($this->itemsData)
        ]);

        $invoice = PurchaseInvoice::find($this->invoiceId);
        if (!$invoice) {
            Log::error('ProcessPurchaseInvoiceItemsJob: Invoice not found', [
                'invoice_id' => $this->invoiceId
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            // Delete existing items if any (for update scenario)
            $invoice->items()->delete();

            $subtotal = 0;
            $vatAmount = 0;
            $total = 0;
            $processedCount = 0;

            // Process items in batches to avoid memory issues
            $batchSize = 50;
            $batches = array_chunk($this->itemsData, $batchSize);

            foreach ($batches as $batchIndex => $batch) {
                Log::info('ProcessPurchaseInvoiceItemsJob: Processing batch', [
                    'invoice_id' => $this->invoiceId,
                    'batch_index' => $batchIndex + 1,
                    'batch_size' => count($batch),
                    'total_batches' => count($batches)
                ]);

                foreach ($batch as $line) {
                    $qty = (float) ($line['quantity'] ?? 0);
                    $unit = (float) ($line['unit_cost'] ?? 0);
                    $base = $qty * $unit;
                    $vat = 0;
                    $vatType = $line['vat_type'] ?? 'no_vat';
                    $rate = (float) ($line['vat_rate'] ?? 0);

                    if ($vatType === 'inclusive' && $rate > 0) {
                        $vat = $base * ($rate / (100 + $rate));
                    } elseif ($vatType === 'exclusive' && $rate > 0) {
                        $vat = $base * ($rate / 100);
                    }
                    $lineTotal = $vatType === 'exclusive' ? $base + $vat : $base;

                    // Determine item type
                    $itemType = $line['item_type'] ?? 'inventory';
                    if (!empty($line['asset_id'])) {
                        $itemType = 'asset';
                    } elseif (!empty($line['inventory_item_id'])) {
                        $itemType = 'inventory';
                    }

                    $assetId = null;

                    // If this is an asset purchase, link to existing asset only
                    if ($itemType === 'asset') {
                        if (empty($line['asset_id'])) {
                            $msg = 'Asset ID is required. Assets must be created separately ' .
                                'before adding to purchase invoice.';
                            throw new \Exception($msg);
                        }
                        $assetId = $line['asset_id'];

                        // Verify asset exists and belongs to company
                        $asset = Asset::where('id', $assetId)
                            ->where('company_id', $invoice->company_id)
                            ->first();

                        if (!$asset) {
                            throw new \Exception(
                                'Selected asset not found or does not belong to your company.'
                            );
                        }
                    }

                    PurchaseInvoiceItem::create([
                        'purchase_invoice_id' => $invoice->id,
                        'item_type' => $itemType,
                        'inventory_item_id' => $itemType === 'inventory' ? ($line['inventory_item_id'] ?? null) : null,
                        'asset_id' => $assetId,
                        'grn_item_id' => $line['grn_item_id'] ?? null,
                        'description' => $line['description'] ?? null,
                        'quantity' => $qty,
                        'unit_cost' => $unit,
                        'vat_type' => $vatType,
                        'vat_rate' => $rate,
                        'vat_amount' => $vat,
                        'line_total' => $lineTotal,
                        'expiry_date' => $itemType === 'inventory' ? ($line['expiry_date'] ?? null) : null,
                        'batch_number' => $itemType === 'inventory' ? ($line['batch_number'] ?? null) : null,
                    ]);

                    $subtotal += ($vatType === 'inclusive') ? ($base - $vat) : $base; // net of VAT
                    $vatAmount += $vat;
                    $total += $lineTotal;
                    $processedCount++;
                }
            }

            // Update invoice totals
            $invoice->update([
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'discount_amount' => $this->discountAmount,
                'withholding_tax_amount' => $this->withholdingTaxAmount,
                'total_amount' => max(0, $subtotal + $vatAmount - $this->discountAmount - $this->withholdingTaxAmount),
                'status' => 'open',
            ]);

            Log::info('ProcessPurchaseInvoiceItemsJob: Items processed successfully', [
                'invoice_id' => $this->invoiceId,
                'processed_count' => $processedCount,
                'total_amount' => $invoice->total_amount
            ]);

            // Reload items relationship
            $invoice->load('items');

            // Post GL transactions
            Log::info('ProcessPurchaseInvoiceItemsJob: Posting GL transactions');
            $invoice->postGlTransactions();

            // Post inventory movements
            Log::info('ProcessPurchaseInvoiceItemsJob: Posting inventory movements');
            $invoice->postInventoryMovements();

            // Update linked assets
            Log::info('ProcessPurchaseInvoiceItemsJob: Updating linked assets');
            $invoice->updateAssetPurchases();

            DB::commit();

            Log::info('ProcessPurchaseInvoiceItemsJob: Completed successfully', [
                'invoice_id' => $this->invoiceId,
                'invoice_number' => $invoice->invoice_number
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ProcessPurchaseInvoiceItemsJob: Failed', [
                'invoice_id' => $this->invoiceId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Let the job retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessPurchaseInvoiceItemsJob: Job failed permanently', [
            'invoice_id' => $this->invoiceId,
            'error' => $exception->getMessage()
        ]);

        // Optionally update invoice status to indicate failure
        $invoice = PurchaseInvoice::find($this->invoiceId);
        if ($invoice) {
            // You could add a 'processing_status' field to track this
            // For now, we'll just log it
        }
    }
}
