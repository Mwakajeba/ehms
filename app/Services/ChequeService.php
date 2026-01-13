<?php

namespace App\Services;

use App\Models\Cheque;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\ChartAccount;
use App\Models\BankAccount;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChequeService
{
    /**
     * Issue a cheque (create cheque record and journal entry)
     * 
     * @param array $data Cheque data
     * @return Cheque
     */
    public function issueCheque(array $data)
    {
        DB::beginTransaction();
        try {
            // Validate cheque number uniqueness
            if (!Cheque::isChequeNumberUnique($data['cheque_number'], $data['bank_account_id'])) {
                throw new \Exception('Cheque number already exists for this bank account.');
            }

            // Create cheque record
            $cheque = Cheque::create([
                'cheque_number' => $data['cheque_number'],
                'cheque_date' => $data['cheque_date'],
                'bank_account_id' => $data['bank_account_id'],
                'payee_name' => $data['payee_name'],
                'amount' => $data['amount'],
                'status' => 'issued',
                'payment_reference_type' => $data['payment_reference_type'] ?? null,
                'payment_reference_id' => $data['payment_reference_id'] ?? null,
                'payment_reference_number' => $data['payment_reference_number'] ?? null,
                'module_origin' => $data['module_origin'] ?? null,
                'payment_type' => $data['payment_type'] ?? null,
                'description' => $data['description'] ?? null,
                'company_id' => $data['company_id'],
                'branch_id' => $data['branch_id'] ?? null,
                'issued_by' => $data['issued_by'] ?? auth()->id(),
            ]);

            // Create journal entry: Dr Expense/Payable, Cr Bank Cheque Issued (Pending)
            $journal = $this->createIssueJournal($cheque, $data);

            $cheque->issue_journal_id = $journal->id;
            $cheque->save();

            DB::commit();

            return $cheque;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cheque issue error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create journal entry when cheque is issued
     */
    protected function createIssueJournal(Cheque $cheque, array $data)
    {
        $bankAccount = BankAccount::findOrFail($cheque->bank_account_id);
        
        // Get cheque issued account (contra account for bank)
        $chequeIssuedAccountId = SystemSetting::getValue('cheque_issued_account_id');
        if (!$chequeIssuedAccountId) {
            // Fallback: try to find by name
            $chequeIssuedAccount = ChartAccount::where('account_name', 'LIKE', '%cheque issued%')
                ->orWhere('account_name', 'LIKE', '%outstanding cheque%')
                ->first();
            
            if (!$chequeIssuedAccount) {
                throw new \Exception('Cheque Issued account not configured. Please set cheque_issued_account_id in system settings.');
            }
            $chequeIssuedAccountId = $chequeIssuedAccount->id;
        }

        // Get expense/payable account from data
        $expenseAccountId = $data['expense_account_id'] ?? $data['payable_account_id'] ?? null;
        if (!$expenseAccountId) {
            throw new \Exception('Expense or Payable account is required for cheque issuance.');
        }

        // Create journal
        $journal = Journal::create([
            'date' => $cheque->cheque_date,
            'reference' => 'CHQ-' . $cheque->cheque_number,
            'reference_type' => 'Cheque Issued',
            'description' => "Cheque issued: {$cheque->cheque_number} - {$cheque->payee_name}",
            'branch_id' => $cheque->branch_id ?? auth()->user()->branch_id,
            'user_id' => $cheque->issued_by ?? auth()->id(),
            'approved' => true,
            'approved_by' => $cheque->issued_by ?? auth()->id(),
            'approved_at' => now(),
        ]);

        // Dr. Expense/Payable
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $expenseAccountId,
            'amount' => $cheque->amount,
            'nature' => 'debit',
            'description' => "Cheque payment: {$cheque->cheque_number}",
        ]);

        // Cr. Cheque Issued (Pending Clearance) - This is a contra account
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $chequeIssuedAccountId,
            'amount' => $cheque->amount,
            'nature' => 'credit',
            'description' => "Cheque issued pending clearance: {$cheque->cheque_number}",
        ]);

        // Initialize approval workflow and create GL transactions
        $journal->initializeApprovalWorkflow();
        $journal->createGlTransactions();

        return $journal;
    }

    /**
     * Clear a cheque (when bank confirms it's cleared)
     */
    public function clearCheque(Cheque $cheque, $clearedBy = null)
    {
        if (!$cheque->canBeCleared()) {
            throw new \Exception('Cheque cannot be cleared in current status.');
        }

        DB::beginTransaction();
        try {
            $bankAccount = $cheque->bankAccount;
            
            // Create journal entry: Dr Cheque Issued Clearing, Cr Bank Account
            $journal = $this->createClearJournal($cheque);

            $cheque->status = 'cleared';
            $cheque->cleared_date = now();
            $cheque->cleared_by = $clearedBy ?? auth()->id();
            $cheque->clear_journal_id = $journal->id;
            $cheque->save();

            DB::commit();

            return $cheque;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cheque clear error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create journal entry when cheque is cleared
     */
    protected function createClearJournal(Cheque $cheque)
    {
        $bankAccount = $cheque->bankAccount;
        
        // Get cheque issued account
        $chequeIssuedAccountId = SystemSetting::getValue('cheque_issued_account_id');
        if (!$chequeIssuedAccountId) {
            $chequeIssuedAccount = ChartAccount::where('account_name', 'LIKE', '%cheque issued%')
                ->orWhere('account_name', 'LIKE', '%outstanding cheque%')
                ->first();
            
            if (!$chequeIssuedAccount) {
                throw new \Exception('Cheque Issued account not configured.');
            }
            $chequeIssuedAccountId = $chequeIssuedAccount->id;
        }

        // Create journal
        $journal = Journal::create([
            'date' => now(),
            'reference' => 'CHQ-CLEAR-' . $cheque->cheque_number,
            'reference_type' => 'Cheque Cleared',
            'description' => "Cheque cleared: {$cheque->cheque_number} - {$cheque->payee_name}",
            'branch_id' => $cheque->branch_id ?? auth()->user()->branch_id,
            'user_id' => auth()->id(),
            'approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Dr. Cheque Issued Clearing (reduce the contra account)
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $chequeIssuedAccountId,
            'amount' => $cheque->amount,
            'nature' => 'debit',
            'description' => "Cheque cleared: {$cheque->cheque_number}",
        ]);

        // Cr. Bank Account (reduce bank balance)
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $bankAccount->chart_account_id,
            'amount' => $cheque->amount,
            'nature' => 'credit',
            'description' => "Cheque cleared: {$cheque->cheque_number}",
        ]);

        // Initialize approval workflow and create GL transactions
        $journal->initializeApprovalWorkflow();
        $journal->createGlTransactions();

        return $journal;
    }

    /**
     * Bounce a cheque (reverse the payment)
     */
    public function bounceCheque(Cheque $cheque, $reason, $bouncedBy = null)
    {
        if ($cheque->status !== 'issued') {
            throw new \Exception('Only issued cheques can be bounced.');
        }

        DB::beginTransaction();
        try {
            // Create journal entry to reverse the original payment
            $journal = $this->createBounceJournal($cheque, $reason);

            $cheque->status = 'bounced';
            $cheque->bounced_date = now();
            $cheque->bounce_reason = $reason;
            $cheque->bounce_journal_id = $journal->id;
            $cheque->save();

            DB::commit();

            return $cheque;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cheque bounce error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create journal entry when cheque bounces
     */
    protected function createBounceJournal(Cheque $cheque, $reason)
    {
        $bankAccount = $cheque->bankAccount;
        
        // Get accounts
        $chequeIssuedAccountId = SystemSetting::getValue('cheque_issued_account_id');
        if (!$chequeIssuedAccountId) {
            $chequeIssuedAccount = ChartAccount::where('account_name', 'LIKE', '%cheque issued%')
                ->first();
            if (!$chequeIssuedAccount) {
                throw new \Exception('Cheque Issued account not configured.');
            }
            $chequeIssuedAccountId = $chequeIssuedAccount->id;
        }

        // Get original expense/payable account from issue journal
        $issueJournal = $cheque->issueJournal;
        if (!$issueJournal) {
            throw new \Exception('Issue journal not found for this cheque.');
        }

        $expenseItem = $issueJournal->items()->where('nature', 'debit')->first();
        if (!$expenseItem) {
            throw new \Exception('Expense account not found in issue journal.');
        }

        // Create journal
        $journal = Journal::create([
            'date' => now(),
            'reference' => 'CHQ-BOUNCE-' . $cheque->cheque_number,
            'reference_type' => 'Cheque Bounced',
            'description' => "Cheque bounced: {$cheque->cheque_number} - {$cheque->payee_name}. Reason: {$reason}",
            'branch_id' => $cheque->branch_id ?? auth()->user()->branch_id,
            'user_id' => auth()->id(),
            'approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Dr. Cheque Issued (reverse the credit)
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $chequeIssuedAccountId,
            'amount' => $cheque->amount,
            'nature' => 'debit',
            'description' => "Cheque bounced: {$cheque->cheque_number}",
        ]);

        // Cr. Expense/Payable (reverse the original debit)
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $expenseItem->chart_account_id,
            'amount' => $cheque->amount,
            'nature' => 'credit',
            'description' => "Cheque bounced reversal: {$cheque->cheque_number}",
        ]);

        // Initialize approval workflow and create GL transactions
        $journal->initializeApprovalWorkflow();
        $journal->createGlTransactions();

        return $journal;
    }

    /**
     * Cancel a cheque
     */
    public function cancelCheque(Cheque $cheque, $reason, $cancelledBy = null)
    {
        if (!$cheque->canBeCancelled()) {
            throw new \Exception('Cheque cannot be cancelled in current status.');
        }

        DB::beginTransaction();
        try {
            // Reverse the original journal entry
            $this->reverseIssueJournal($cheque, $reason);

            $cheque->status = 'cancelled';
            $cheque->cancelled_date = now();
            $cheque->cancellation_reason = $reason;
            $cheque->cancelled_by = $cancelledBy ?? auth()->id();
            $cheque->save();

            DB::commit();

            return $cheque;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cheque cancel error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the issue journal when cheque is cancelled
     */
    protected function reverseIssueJournal(Cheque $cheque, $reason)
    {
        $issueJournal = $cheque->issueJournal;
        if (!$issueJournal) {
            throw new \Exception('Issue journal not found for this cheque.');
        }

        // Create reversing journal entry
        $journal = Journal::create([
            'date' => now(),
            'reference' => 'CHQ-CANCEL-' . $cheque->cheque_number,
            'reference_type' => 'Cheque Cancelled',
            'description' => "Cheque cancelled: {$cheque->cheque_number} - {$cheque->payee_name}. Reason: {$reason}",
            'branch_id' => $cheque->branch_id ?? auth()->user()->branch_id,
            'user_id' => auth()->id(),
            'approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Reverse all items from issue journal
        foreach ($issueJournal->items as $item) {
            JournalItem::create([
                'journal_id' => $journal->id,
                'chart_account_id' => $item->chart_account_id,
                'amount' => $item->amount,
                'nature' => $item->nature === 'debit' ? 'credit' : 'debit', // Reverse nature
                'description' => "Cheque cancellation reversal: {$cheque->cheque_number}",
            ]);
        }

        // Initialize approval workflow and create GL transactions
        $journal->initializeApprovalWorkflow();
        $journal->createGlTransactions();

        return $journal;
    }

    /**
     * Mark cheque as stale
     */
    public function markStale(Cheque $cheque, $days = 180)
    {
        if ($cheque->status !== 'issued') {
            return false;
        }

        if ($cheque->isStale($days)) {
            $cheque->status = 'stale';
            $cheque->save();
            return true;
        }

        return false;
    }

    /**
     * Get outstanding cheques for bank reconciliation
     */
    public function getOutstandingCheques($bankAccountId = null, $companyId = null)
    {
        $query = Cheque::where('status', 'issued');

        if ($bankAccountId) {
            $query->where('bank_account_id', $bankAccountId);
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('cheque_date')->get();
    }
}

