<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;

class DeleteAllFeeInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:delete-all {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all fee invoices and their items from the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting fee invoices deletion process...');

        if (!$this->option('force') && !$this->confirm('This will permanently delete ALL fee invoices and invoice items. Are you sure you want to proceed?')) {
            $this->info('Deletion cancelled.');
            return Command::SUCCESS;
        }

        DB::beginTransaction();
        try {
            $invoiceCount = FeeInvoice::count();
            $invoiceItemCount = FeeInvoiceItem::count();

            $this->info("Found: {$invoiceCount} fee invoices, {$invoiceItemCount} invoice items");

            // 1. Delete fee invoice items first (must be before invoices if no cascade delete is set)
            $this->info('Deleting fee invoice items...');
            $deletedInvoiceItems = FeeInvoiceItem::query()->delete();
            $this->info("Deleted {$deletedInvoiceItems} invoice items");

            // 2. Delete fee invoices
            $this->info('Deleting fee invoices...');
            $deletedInvoices = FeeInvoice::query()->delete();
            $this->info("Deleted {$deletedInvoices} fee invoices");

            DB::commit();
            $this->info('✅ Successfully deleted all fee invoices!');
            $this->table(
                ['Category', 'Count'],
                [
                    ['Fee Invoices', $deletedInvoices],
                    ['Invoice Items', $deletedInvoiceItems],
                ]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ An error occurred during deletion: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
