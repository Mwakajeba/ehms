<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\FeeInvoice;

class StudentPrepaidAccount extends Model
{
    protected $fillable = [
        'student_id',
        'credit_balance',
        'total_deposited',
        'total_used',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_balance' => 'decimal:2',
        'total_deposited' => 'decimal:2',
        'total_used' => 'decimal:2',
    ];

    /**
     * Get the student that owns the prepaid account.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the company that owns the prepaid account.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the prepaid account.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the user who created the prepaid account.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the prepaid account.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Get the transactions for this prepaid account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(StudentPrepaidAccountTransaction::class, 'prepaid_account_id');
    }

    /**
     * Add credit to the account.
     */
    public function addCredit(float $amount, string $reference = null, $paymentId = null, string $notes = null): StudentPrepaidAccountTransaction
    {
        $balanceBefore = $this->credit_balance;
        $this->credit_balance += $amount;
        $this->total_deposited += $amount;
        $this->updated_by = auth()->id();
        $this->save();

        return $this->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->credit_balance,
            'reference' => $reference,
            'payment_id' => $paymentId,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Use credit from the account (for invoice payment).
     */
    public function useCredit(float $amount, $invoiceId = null, string $notes = null): StudentPrepaidAccountTransaction
    {
        $balanceBefore = $this->credit_balance;
        $usedAmount = min($amount, $this->credit_balance); // Can't use more than available
        
        $this->credit_balance -= $usedAmount;
        $this->total_used += $usedAmount;
        $this->updated_by = auth()->id();
        $this->save();

        return $this->transactions()->create([
            'type' => 'invoice_application',
            'amount' => $usedAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->credit_balance,
            'fee_invoice_id' => $invoiceId,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get or create prepaid account for a student.
     */
    public static function getOrCreateForStudent($studentId, $companyId, $branchId = null): self
    {
        return self::firstOrCreate(
            ['student_id' => $studentId],
            [
                'credit_balance' => 0,
                'total_deposited' => 0,
                'total_used' => 0,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => auth()->id(),
            ]
        );
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
        });
    }

    /**
     * Automatically apply available credit to unpaid invoices.
     * Returns array with information about what was applied.
     */
    public function autoApplyCreditToUnpaidInvoices(): array
    {
        $result = [
            'applied' => false,
            'total_applied' => 0,
            'invoices_paid' => [],
            'message' => ''
        ];

        try {
            // Check if auto-apply is enabled
            $autoApply = \App\Models\SystemSetting::getValue('prepaid_auto_apply_credit', true);
            if (!$autoApply) {
                return $result; // Auto-apply is disabled
            }

            if ($this->credit_balance <= 0) {
                return $result; // No credit available
            }

            // Get unpaid invoices for this student, ordered by period (oldest first)
            $unpaidInvoices = \App\Models\FeeInvoice::where('student_id', $this->student_id)
                ->where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->whereRaw('(total_amount - COALESCE(paid_amount, 0)) > 0') // Has outstanding amount
                ->orderBy('period', 'asc')
                ->orderBy('issue_date', 'asc')
                ->get();

            if ($unpaidInvoices->isEmpty()) {
                return $result; // No unpaid invoices
            }

            $remainingCredit = $this->credit_balance;
            $totalApplied = 0;
            $invoicesPaid = [];

            foreach ($unpaidInvoices as $invoice) {
                if ($remainingCredit <= 0) {
                    break; // No more credit to apply
                }

                // Calculate outstanding amount
                $outstandingAmount = $invoice->total_amount - ($invoice->paid_amount ?? 0);
                
                if ($outstandingAmount <= 0) {
                    continue; // Invoice already fully paid
                }

                // Apply credit (up to outstanding amount)
                $creditToApply = min($remainingCredit, $outstandingAmount);
                
                if ($creditToApply > 0) {
                    // Use credit from account
                    $transaction = $this->useCredit($creditToApply, $invoice->id, "Auto-applied to invoice {$invoice->invoice_number}");

                    // Get prepaid chart account from settings
                    $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
                    if ($prepaidAccountId) {
                        $receivableAccountId = $invoice->feeGroup->receivable_account_id ??
                                             \App\Models\ChartAccount::where('account_name', 'LIKE', '%Trade Receivable%')
                                                 ->orWhere('account_name', 'LIKE', '%Accounts Receivable%')
                                                 ->value('id') ?? 18;

                        $userId = auth()->id();
                        $branchId = $this->branch_id ?? session('branch_id') ?? auth()->user()->branch_id;

                        // Create GL transactions
                        // 1. Debit Prepaid Account
                        \App\Models\GlTransaction::create([
                            'chart_account_id' => $prepaidAccountId,
                            'customer_id' => null,
                            'supplier_id' => null,
                            'amount' => $creditToApply,
                            'nature' => 'debit',
                            'transaction_id' => $transaction->id,
                            'transaction_type' => 'student_prepaid_application',
                            'date' => $invoice->issue_date ?? now(),
                            'description' => "Prepaid credit applied to invoice {$invoice->invoice_number}",
                            'branch_id' => $branchId,
                            'user_id' => $userId,
                        ]);

                        // 2. Credit Accounts Receivable
                        \App\Models\GlTransaction::create([
                            'chart_account_id' => $receivableAccountId,
                            'customer_id' => null,
                            'supplier_id' => null,
                            'amount' => $creditToApply,
                            'nature' => 'credit',
                            'transaction_id' => $transaction->id,
                            'transaction_type' => 'student_prepaid_application',
                            'date' => $invoice->issue_date ?? now(),
                            'description' => "Prepaid credit applied to invoice {$invoice->invoice_number}",
                            'branch_id' => $branchId,
                            'user_id' => $userId,
                        ]);
                    }

                    // Update invoice paid amount
                    $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $creditToApply;
                    
                    // Update invoice status if fully paid
                    if ($invoice->paid_amount >= $invoice->total_amount) {
                        $invoice->status = 'paid';
                    }
                    
                    $invoice->save();

                    $remainingCredit -= $creditToApply;
                    $totalApplied += $creditToApply;
                    $invoicesPaid[] = [
                        'invoice_number' => $invoice->invoice_number,
                        'amount_applied' => $creditToApply,
                        'period' => $invoice->period,
                    ];
                }
            }

            if ($totalApplied > 0) {
                $result = [
                    'applied' => true,
                    'total_applied' => $totalApplied,
                    'invoices_paid' => $invoicesPaid,
                    'message' => "TZS " . number_format($totalApplied, 2) . " was automatically applied to " . count($invoicesPaid) . " unpaid invoice(s)."
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to auto-apply prepaid credit to unpaid invoices', [
                'prepaid_account_id' => $this->id,
                'student_id' => $this->student_id,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Get the hashid attribute.
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->getKey());
    }
}

