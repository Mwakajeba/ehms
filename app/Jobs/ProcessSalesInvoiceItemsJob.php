<?php

namespace App\Jobs;

use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceItem;
use App\Models\Sales\SalesOrder;
use App\Models\Inventory\Item as InventoryItem;
use App\Models\Inventory\Movement as InventoryMovement;
use App\Models\Customer;
use App\Services\InventoryCostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSalesInvoiceItemsJob implements ShouldQueue
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
        private ?int $salesOrderId = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('ProcessSalesInvoiceItemsJob: Starting', [
            'invoice_id' => $this->invoiceId,
            'items_count' => count($this->itemsData)
        ]);

        $invoice = SalesInvoice::find($this->invoiceId);
        if (!$invoice) {
            Log::error('ProcessSalesInvoiceItemsJob: Invoice not found', [
                'invoice_id' => $this->invoiceId
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            // Delete existing items if any (for update scenario)
            $invoice->items()->delete();

            $processedCount = 0;

            // Consolidate items by inventory_item_id before creating invoice items
            $consolidatedItems = [];
            foreach ($this->itemsData as $itemData) {
                $inventoryItemId = $itemData['inventory_item_id'];

                if (isset($consolidatedItems[$inventoryItemId])) {
                    // Item already exists, add to quantity
                    $consolidatedItems[$inventoryItemId]['quantity'] += $itemData['quantity'];
                } else {
                    // New item, add to consolidated items
                    $consolidatedItems[$inventoryItemId] = $itemData;
                }
            }

            // Process items in batches to avoid memory issues
            $batchSize = 50;
            $batches = array_chunk($consolidatedItems, $batchSize, true);

            foreach ($batches as $batchIndex => $batch) {
                Log::info('ProcessSalesInvoiceItemsJob: Processing batch', [
                    'invoice_id' => $this->invoiceId,
                    'batch_index' => $batchIndex + 1,
                    'batch_size' => count($batch),
                    'total_batches' => count($batches)
                ]);

                foreach ($batch as $itemData) {
                    $inventoryItem = InventoryItem::find($itemData['inventory_item_id']);
                    if (!$inventoryItem) {
                        Log::warning('ProcessSalesInvoiceItemsJob: Inventory item not found', [
                            'invoice_id' => $this->invoiceId,
                            'inventory_item_id' => $itemData['inventory_item_id']
                        ]);
                        continue;
                    }

                    // Calculate line total before creating the item
                    $quantity = $itemData['quantity'];
                    $unitPrice = $itemData['unit_price'];
                    $vatType = $itemData['vat_type'];
                    $vatRate = $itemData['vat_rate'];

                    // Calculate subtotal (no item-level discounts)
                    $subtotal = $quantity * $unitPrice;

                    // Calculate VAT and line total
                    $vatAmount = 0;
                    $lineTotal = 0;

                    if ($vatType === 'no_vat') {
                        $lineTotal = $subtotal;
                    } elseif ($vatType === 'exclusive') {
                        $vatAmount = $subtotal * ($vatRate / 100);
                        $lineTotal = $subtotal + $vatAmount;
                    } else {
                        // VAT inclusive
                        $vatAmount = $subtotal * ($vatRate / (100 + $vatRate));
                        $lineTotal = $subtotal;
                    }

                    $invoiceItem = SalesInvoiceItem::create([
                        'sales_invoice_id' => $invoice->id,
                        'inventory_item_id' => $itemData['inventory_item_id'],
                        'item_name' => $inventoryItem->name,
                        'item_code' => $inventoryItem->code,
                        'description' => $inventoryItem->description,
                        'unit_of_measure' => $inventoryItem->unit_of_measure,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'vat_type' => $vatType,
                        'vat_rate' => $vatRate,
                        'vat_amount' => $vatAmount,
                        'discount_type' => null,
                        'discount_rate' => 0,
                        'discount_amount' => 0,
                        'notes' => $itemData['notes'] ?? null,
                    ]);

                    // Check stock availability
                    $invoiceItem->checkStockAvailability();
                    $invoiceItem->save();

                    // Create inventory movement for stock out (only for products)
                    if ($inventoryItem->track_stock && $inventoryItem->item_type === 'product') {
                        $costService = new InventoryCostService();
                        // Get stock as of invoice date (for backdated invoices, this ensures correct balance calculation)
                        // Use the invoice's created_at timestamp to exclude same-day transactions that happened after this invoice
                        $stockService = new \App\Services\InventoryStockService();
                        $asOfTimestamp = $invoice->created_at ? $invoice->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
                        $balanceBefore = $stockService->getItemStockAtLocationAsOfDate(
                            $inventoryItem->id, 
                            session('location_id'), 
                            $invoice->invoice_date,
                            null,
                            $asOfTimestamp
                        );
                        $balanceAfter = $balanceBefore - $quantity;

                        // Get actual cost using FIFO/Weighted Average
                        $costInfo = $costService->removeInventory(
                            $inventoryItem->id,
                            $quantity,
                            'sale',
                            'Sales Invoice: ' . $invoice->invoice_number,
                            $invoice->invoice_date
                        );

                        InventoryMovement::create([
                            'item_id' => $inventoryItem->id,
                            'user_id' => $invoice->created_by,
                            'branch_id' => $invoice->branch_id,
                            'location_id' => session('location_id'),
                            'movement_type' => 'sold',
                            'quantity' => $quantity,
                            'unit_price' => $costInfo['average_unit_cost'],
                            'unit_cost' => $costInfo['average_unit_cost'],
                            'total_cost' => $costInfo['total_cost'],
                            'balance_before' => $balanceBefore,
                            'balance_after' => $balanceAfter,
                            'reference' => 'Sales Invoice: ' . $invoice->invoice_number,
                            'reference_type' => 'sales_invoice',
                            'reference_id' => $invoice->id,
                            'notes' => 'Stock sold via sales invoice',
                            'movement_date' => $invoice->invoice_date,
                        ]);

                        // Consume stock using FEFO if item tracks expiry
                        $consumedLayers = [];
                        $earliestExpiryDate = null;
                        $batchNumbers = [];

                        if ($inventoryItem->track_expiry) {
                            $expiryService = new \App\Services\ExpiryStockService();
                            $consumedLayers = $expiryService->consumeStock(
                                $inventoryItem->id,
                                session('location_id'),
                                $quantity,
                                'FEFO'
                            );

                            // Extract expiry information for display
                            if (!empty($consumedLayers)) {
                                $earliestExpiryDate = $consumedLayers[0]['expiry_date'];
                                $batchNumbers = array_column($consumedLayers, 'batch_number');
                            }

                            // Log consumed layers for audit trail
                            foreach ($consumedLayers as $layer) {
                                Log::info('Sales Invoice: Consumed stock with expiry', [
                                    'invoice_id' => $invoice->id,
                                    'item_id' => $inventoryItem->id,
                                    'batch_number' => $layer['batch_number'],
                                    'expiry_date' => $layer['expiry_date'],
                                    'quantity' => $layer['quantity'],
                                    'unit_cost' => $layer['unit_cost']
                                ]);
                            }
                        }

                        // Update invoice item with expiry information
                        $invoiceItem->update([
                            'batch_number' => !empty($batchNumbers) ? implode(', ', $batchNumbers) : null,
                            'expiry_date' => $earliestExpiryDate,
                            'expiry_consumption_details' => $consumedLayers,
                        ]);
                    }

                    $processedCount++;
                }
            }

            // Update invoice totals
            $invoice->updateTotals();
            $invoice->refresh();

            // Check credit limit (only if customer has a credit limit set)
            $customer = Customer::find($invoice->customer_id);
            if ($customer && $customer->credit_limit > 0) {
                $currentBalance = $customer->getCurrentBalance();
                $availableCredit = $customer->getAvailableCredit();
                $invoiceTotal = $invoice->total_amount;

                // Check if invoice total exceeds available credit
                if ($invoiceTotal > $availableCredit) {
                    $excessAmount = $invoiceTotal - $availableCredit;
                    $message = "Invoice amount (TZS " . number_format($invoiceTotal, 2) . ") exceeds available credit. " .
                               "Credit Limit: TZS " . number_format($customer->credit_limit, 2) . ", " .
                               "Current Balance: TZS " . number_format($currentBalance, 2) . ", " .
                               "Available Credit: TZS " . number_format($availableCredit, 2) . ". " .
                               "Excess: TZS " . number_format($excessAmount, 2);

                    Log::warning("Credit limit exceeded for customer {$customer->id}: {$message}");
                    throw new \Exception($message);
                }
            }

            // Create double-entry transactions
            $invoice->createDoubleEntryTransactions();

            // Update sales order status if invoice was created from an order
            if ($this->salesOrderId) {
                $salesOrder = SalesOrder::find($this->salesOrderId);
                if ($salesOrder) {
                    $salesOrder->update([
                        'status' => 'converted_to_invoice',
                        'updated_by' => $invoice->created_by
                    ]);
                }
            }

            DB::commit();

            Log::info('ProcessSalesInvoiceItemsJob: Completed successfully', [
                'invoice_id' => $this->invoiceId,
                'invoice_number' => $invoice->invoice_number,
                'processed_count' => $processedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ProcessSalesInvoiceItemsJob: Failed', [
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
        Log::error('ProcessSalesInvoiceItemsJob: Job failed permanently', [
            'invoice_id' => $this->invoiceId,
            'error' => $exception->getMessage()
        ]);

        // Optionally update invoice status to indicate failure
        $invoice = SalesInvoice::find($this->invoiceId);
        if ($invoice) {
            // You could add a 'processing_status' field to track this
            // For now, we'll just log it
        }
    }
}

