<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\LipishaPaymentLog;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\GlTransaction;
use App\Models\FeeInvoice;
use App\Models\College\FeeInvoice as CollegeFeeInvoice;
use App\Models\BankAccount;

class WebhookController extends Controller
{
    /**
     * Handle Lipisha webhook notifications.
     */
    public function lipisha(Request $request)
    {
        $signature = $request->header('x-webhook-signature');
        $verifyToken = config('services.lipisha.webhook_verify_token'); // Set this in config/services.php
        $payload = $request->getContent();

        // Signature validation bypassed for testing
        // $expectedSignature = hash_hmac('sha256', $payload, $verifyToken);
        // if (!$signature || !hash_equals($expectedSignature, $signature)) {
        //     Log::warning('Lipisha Webhook: Invalid signature', [
        //         'received' => $signature,
        //         'expected' => $expectedSignature,
        //         'payload' => $payload,
        //     ]);
        //     return response()->json(['error' => 'Invalid signature'], 400);
        // }

        $data = $request->json()->all();
        Log::info('Lipisha Webhook received', $data);

        DB::beginTransaction();
        try {
            // Store payment log
            $paymentLog = LipishaPaymentLog::create([
                'bill_number' => $data['bill_number'] ?? null,
                'amount' => $data['amount'] ?? null,
                'receipt' => $data['receipt'] ?? null,
                'transaction_ref' => $data['transactionRef'] ?? null,
                'transaction_date' => isset($data['transactionDate']) ? $data['transactionDate'] : now(),
                'bill_id' => $data['bill_id'] ?? null,
                'payment_id' => $data['paymentID'] ?? null,
                'metadata' => isset($data['metadata']) ? $data['metadata'] : null,
                'raw_payload' => $payload,
                'status' => 'pending',
            ]);

            // Find invoice by bill_number (lipisha_control_number)
            $billNumber = $data['bill_number'] ?? null;
            if (!$billNumber) {
                throw new \Exception('Bill number is required');
            }

            // Try to find School Fee Invoice first
            $invoice = FeeInvoice::where('lipisha_control_number', $billNumber)->first();
            $invoiceType = 'school_fee_invoice';
            
            // If not found, try College Fee Invoice
            if (!$invoice) {
                $invoice = CollegeFeeInvoice::where('lipisha_control_number', $billNumber)->first();
                $invoiceType = 'college_fee_invoice';
            }

            if (!$invoice) {
                throw new \Exception("Invoice not found for bill number: {$billNumber}");
            }

            // Check if payment already processed
            $existingReceipt = Receipt::where('reference_number', $data['transactionRef'] ?? null)
                ->where('reference_type', $invoiceType)
                ->first();

            if ($existingReceipt) {
                Log::warning('Lipisha Webhook: Payment already processed', [
                    'transaction_ref' => $data['transactionRef'],
                    'receipt_id' => $existingReceipt->id,
                ]);
                $paymentLog->update(['status' => 'processed', 'error_message' => 'Payment already processed']);
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Payment already processed'], 200);
            }

            $amount = floatval($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new \Exception('Invalid payment amount');
            }

            // Get metadata
            $metadata = $data['metadata'] ?? [];
            $bankAccountId = $metadata['bank_account_id'] ?? null;
            $userId = $metadata['user_id'] ?? auth()->id() ?? 1;
            $branchId = $metadata['branch_id'] ?? $invoice->branch_id ?? null;

            // Get bank account
            $bankAccount = null;
            if ($bankAccountId) {
                $bankAccount = BankAccount::find($bankAccountId);
            }

            // Get student name
            $studentName = 'Unknown';
            if ($invoiceType === 'school_fee_invoice') {
                $invoice->load('student');
                $studentName = $invoice->student ? ($invoice->student->first_name . ' ' . $invoice->student->last_name) : 'Unknown';
            } else {
                $invoice->load('student');
                $studentName = $invoice->student ? $invoice->student->full_name : 'Unknown';
            }

            // Set description
            $description = 'Lipisha Payment: Control No. ' . ($billNumber ?? '-') . ', Receipt: ' . ($data['receipt'] ?? '-') . ', Amount: ' . number_format($amount, 2) . ', Student: ' . $studentName;

            // Generate receipt reference
            $receiptNumber = 'RCP-' . date('Y') . '-' . str_pad(Receipt::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create receipt
            $receipt = Receipt::create([
                'reference' => $receiptNumber,
                'reference_type' => $invoiceType,
                'reference_number' => $invoice->invoice_number,
                'amount' => $amount,
                'currency' => config('app.currency', 'TZS'),
                'date' => isset($data['transactionDate']) ? $data['transactionDate'] : now(),
                'description' => $description,
                'user_id' => $userId,
                'bank_account_id' => $bankAccountId,
                'payee_type' => 'customer',
                'payee_id' => $invoice->student_id,
                'payee_name' => $studentName,
                'branch_id' => $branchId,
                'approved' => true,
                'approved_by' => $userId,
                'approved_at' => now(),
                'payment_method' => 'bank',
            ]);

            // Create receipt items
            if ($invoiceType === 'college_fee_invoice') {
                $invoice->load('feeInvoiceItems', 'feeGroup');
                $hasItems = false;
                
                foreach ($invoice->feeInvoiceItems as $invoiceItem) {
                    // Calculate the proportion of this payment that applies to this item
                    $itemProportion = $invoice->total_amount > 0 ? ($invoiceItem->amount / $invoice->total_amount) : 0;
                    $itemPaymentAmount = round($amount * $itemProportion, 2);

                    // Get the income account from the fee group
                    $incomeAccountId = $invoice->feeGroup->income_account_id ?? null;

                    if ($incomeAccountId && $itemPaymentAmount > 0) {
                        ReceiptItem::create([
                            'receipt_id' => $receipt->id,
                            'chart_account_id' => $incomeAccountId,
                            'amount' => $itemPaymentAmount,
                            'base_amount' => $itemPaymentAmount,
                            'description' => $invoiceItem->description ?? 'Fee payment - ' . $studentName,
                        ]);
                        $hasItems = true;
                    }
                }

                // If there are any rounding differences, adjust the last item
                $totalReceiptItems = $receipt->receiptItems()->sum('amount');
                if (abs($totalReceiptItems - $amount) > 0.01 && $hasItems) {
                    $difference = $amount - $totalReceiptItems;
                    $lastItem = $receipt->receiptItems()->latest()->first();
                    if ($lastItem) {
                        $lastItem->update([
                            'amount' => $lastItem->amount + $difference,
                            'base_amount' => $lastItem->base_amount + $difference,
                        ]);
                    }
                }

                // If no items were created, create a single receipt item
                if (!$hasItems) {
                    $incomeAccountId = $invoice->feeGroup->income_account_id ?? null;
                    if ($incomeAccountId) {
                        ReceiptItem::create([
                            'receipt_id' => $receipt->id,
                            'chart_account_id' => $incomeAccountId,
                            'amount' => $amount,
                            'base_amount' => $amount,
                            'description' => 'Fee payment - ' . $studentName,
                        ]);
                    }
                }
            } else {
                // School fee invoice - try to use items if available, otherwise use fee group income account
                $invoice->load('items', 'feeGroup');
                $hasItems = false;
                
                if ($invoice->items && $invoice->items->count() > 0) {
                    foreach ($invoice->items as $invoiceItem) {
                        // Calculate the proportion of this payment that applies to this item
                        $itemProportion = $invoice->total_amount > 0 ? ($invoiceItem->amount / $invoice->total_amount) : 0;
                        $itemPaymentAmount = round($amount * $itemProportion, 2);

                        // Get the income account from the fee group
                        $incomeAccountId = $invoice->feeGroup->income_account_id ?? null;

                        if ($incomeAccountId && $itemPaymentAmount > 0) {
                            ReceiptItem::create([
                                'receipt_id' => $receipt->id,
                                'chart_account_id' => $incomeAccountId,
                                'amount' => $itemPaymentAmount,
                                'base_amount' => $itemPaymentAmount,
                                'description' => ($invoiceItem->fee_name ?? 'Fee payment') . ' - ' . $studentName,
                            ]);
                            $hasItems = true;
                        }
                    }

                    // If there are any rounding differences, adjust the last item
                    $totalReceiptItems = $receipt->receiptItems()->sum('amount');
                    if (abs($totalReceiptItems - $amount) > 0.01 && $hasItems) {
                        $difference = $amount - $totalReceiptItems;
                        $lastItem = $receipt->receiptItems()->latest()->first();
                        if ($lastItem) {
                            $lastItem->update([
                                'amount' => $lastItem->amount + $difference,
                                'base_amount' => $lastItem->base_amount + $difference,
                            ]);
                        }
                    }
                }

                // If no items were created, create a single receipt item
                if (!$hasItems) {
                    $incomeAccountId = $invoice->feeGroup->income_account_id ?? null;
                    if ($incomeAccountId) {
                        ReceiptItem::create([
                            'receipt_id' => $receipt->id,
                            'chart_account_id' => $incomeAccountId,
                            'amount' => $amount,
                            'base_amount' => $amount,
                            'description' => 'Fee payment - ' . $studentName,
                        ]);
                    }
                }
            }

            // Create GL transactions through receipt
            $receipt->createGlTransactions();

            // Update invoice paid amount
            $newPaidAmount = $invoice->paid_amount + $amount;
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newPaidAmount >= $invoice->total_amount ? 'paid' : 'issued',
            ]);

            // Update payment log status
            $paymentLog->update(['status' => 'processed']);

            DB::commit();

            Log::info('Lipisha Webhook: Payment processed successfully', [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoiceType,
                'receipt_id' => $receipt->id,
                'amount' => $amount,
            ]);

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Lipisha Webhook: Error processing payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            // Update payment log with error
            if (isset($paymentLog)) {
                $paymentLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

