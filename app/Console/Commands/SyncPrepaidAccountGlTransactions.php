<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School\StudentPrepaidAccount;
use App\Models\School\StudentPrepaidAccountTransaction;
use App\Models\GlTransaction;
use App\Models\FeeInvoice;
use Illuminate\Support\Facades\DB;

class SyncPrepaidAccountGlTransactions extends Command
{
    protected $signature = 'prepaid:sync-gl-transactions {--fix : Fix missing GL transactions}';
    protected $description = 'Check and sync GL transactions for prepaid account applications';

    public function handle()
    {
        $this->info('Checking prepaid account GL transactions...');
        
        $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
        if (!$prepaidAccountId) {
            $this->error('Prepaid chart account not configured. Please set it in settings.');
            return 1;
        }

        $accounts = StudentPrepaidAccount::with('transactions')->get();
        $missingCount = 0;
        $fixedCount = 0;

        foreach ($accounts as $account) {
            // Get all invoice application transactions
            $applicationTransactions = $account->transactions()
                ->where('type', 'invoice_application')
                ->get();

            foreach ($applicationTransactions as $trans) {
                // Check if GL transactions exist for this application
                $glTransactions = GlTransaction::where('transaction_type', 'student_prepaid_application')
                    ->where('transaction_id', $trans->id)
                    ->get();

                if ($glTransactions->isEmpty()) {
                    $missingCount++;
                    $this->warn("Missing GL transactions for transaction ID: {$trans->id} (Account: {$account->id}, Amount: {$trans->amount})");

                    if ($this->option('fix')) {
                        // Get the invoice
                        $invoice = null;
                        if ($trans->fee_invoice_id) {
                            $invoice = FeeInvoice::find($trans->fee_invoice_id);
                        }

                        if ($invoice) {
                            $receivableAccountId = $invoice->feeGroup->receivable_account_id ??
                                                 \App\Models\ChartAccount::where('account_name', 'LIKE', '%Trade Receivable%')
                                                     ->orWhere('account_name', 'LIKE', '%Accounts Receivable%')
                                                     ->value('id') ?? 18;

                            $userId = $trans->created_by ?? 1;
                            $branchId = $account->branch_id ?? null;

                            // Create GL transactions
                            // 1. Debit Prepaid Account
                            GlTransaction::create([
                                'chart_account_id' => $prepaidAccountId,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $trans->amount,
                                'nature' => 'debit',
                                'transaction_id' => $trans->id,
                                'transaction_type' => 'student_prepaid_application',
                                'date' => $trans->created_at ?? now(),
                                'description' => $trans->notes ?? "Prepaid credit applied to invoice {$invoice->invoice_number}",
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                            ]);

                            // 2. Credit Accounts Receivable
                            GlTransaction::create([
                                'chart_account_id' => $receivableAccountId,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $trans->amount,
                                'nature' => 'credit',
                                'transaction_id' => $trans->id,
                                'transaction_type' => 'student_prepaid_application',
                                'date' => $trans->created_at ?? now(),
                                'description' => $trans->notes ?? "Prepaid credit applied to invoice {$invoice->invoice_number}",
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                            ]);

                            $fixedCount++;
                            $this->info("Fixed GL transactions for transaction ID: {$trans->id}");
                        } else {
                            $this->error("Cannot fix: Invoice not found for transaction ID: {$trans->id}");
                        }
                    }
                } elseif ($glTransactions->count() != 2) {
                    $this->warn("Incomplete GL transactions for transaction ID: {$trans->id} (Found: {$glTransactions->count()}, Expected: 2)");
                }
            }
        }

        $this->info("Summary:");
        $this->info("- Missing GL transactions: {$missingCount}");
        if ($this->option('fix')) {
            $this->info("- Fixed GL transactions: {$fixedCount}");
        } else {
            $this->info("Run with --fix to fix missing GL transactions");
        }

        return 0;
    }
}

