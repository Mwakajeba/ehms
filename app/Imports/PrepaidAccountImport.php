<?php

namespace App\Imports;

use App\Models\School\StudentPrepaidAccount;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\BankAccount;
use App\Models\GlTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrepaidAccountImport implements ToCollection, WithHeadingRow
{
    protected $class;
    protected $companyId;
    protected $branchId;
    protected $bankAccountId;
    protected $userId;
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    public function __construct(Classe $class, $companyId, $branchId, $bankAccountId, $userId)
    {
        $this->class = $class;
        $this->companyId = $companyId;
        $this->branchId = $branchId;
        $this->bankAccountId = $bankAccountId;
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because of header row and 0-based index

            try {
                $rowArray = $row->toArray();

                // Get admission number (try multiple key formats)
                $admissionNumber = '';
                if (isset($rowArray['admission_number'])) {
                    $admissionNumber = trim($rowArray['admission_number']);
                } elseif (isset($rowArray['admission number'])) {
                    $admissionNumber = trim($rowArray['admission number']);
                } else {
                    $values = array_values($rowArray);
                    $admissionNumber = isset($values[0]) ? trim($values[0]) : '';
                }

                if (empty($admissionNumber)) {
                    continue; // Skip empty rows
                }

                // Find student
                $student = Student::where('admission_number', $admissionNumber)
                    ->where('class_id', $this->class->id)
                    ->where('company_id', $this->companyId)
                    ->first();

                if (!$student) {
                    $this->errors[] = "Row {$rowNumber}: Student with admission number '{$admissionNumber}' not found in class '{$this->class->name}'";
                    $this->errorCount++;
                    continue;
                }

                // Get amount (try multiple key formats)
                $amount = 0;
                if (isset($rowArray['amount'])) {
                    $amount = $rowArray['amount'];
                } elseif (isset($rowArray['Amount'])) {
                    $amount = $rowArray['Amount'];
                } else {
                    $values = array_values($rowArray);
                    $amount = isset($values[2]) ? $values[2] : 0; // Amount is 3rd column (index 2)
                }

                $amount = is_numeric($amount) ? (float)$amount : 0;
                if ($amount <= 0) {
                    $this->errors[] = "Row {$rowNumber}: Amount must be greater than 0";
                    $this->errorCount++;
                    continue;
                }

                // Get reference (optional)
                $reference = '';
                if (isset($rowArray['reference'])) {
                    $reference = trim($rowArray['reference']);
                } elseif (isset($rowArray['Reference'])) {
                    $reference = trim($rowArray['Reference']);
                } else {
                    $values = array_values($rowArray);
                    $reference = isset($values[3]) ? trim($values[3]) : '';
                }

                // Get notes (optional)
                $notes = '';
                if (isset($rowArray['notes'])) {
                    $notes = trim($rowArray['notes']);
                } elseif (isset($rowArray['Notes'])) {
                    $notes = trim($rowArray['Notes']);
                } else {
                    $values = array_values($rowArray);
                    $notes = isset($values[4]) ? trim($values[4]) : '';
                }

                // Get or create prepaid account
                $account = StudentPrepaidAccount::getOrCreateForStudent(
                    $student->id,
                    $this->companyId,
                    $this->branchId
                );

                // Add credit
                $transaction = $account->addCredit(
                    $amount,
                    $reference ?: null,
                    null,
                    $notes ?: null
                );

                // Get bank account
                $bankAccount = BankAccount::find($this->bankAccountId);
                if (!$bankAccount) {
                    throw new \Exception('Bank account not found');
                }

                // Get prepaid chart account from settings
                $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
                if (!$prepaidAccountId) {
                    throw new \Exception('Prepaid chart account not configured. Please set it in settings.');
                }

                // Create GL transactions
                // 1. Debit Bank Account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => null,
                    'supplier_id' => null,
                    'amount' => $amount,
                    'nature' => 'debit',
                    'transaction_id' => $transaction->id,
                    'transaction_type' => 'student_prepaid_deposit',
                    'date' => now(),
                    'description' => $notes ?: "Prepaid account deposit for student {$student->first_name} {$student->last_name}",
                    'branch_id' => $this->branchId,
                    'user_id' => $this->userId,
                ]);

                // 2. Credit Prepaid Account
                GlTransaction::create([
                    'chart_account_id' => $prepaidAccountId,
                    'customer_id' => null,
                    'supplier_id' => null,
                    'amount' => $amount,
                    'nature' => 'credit',
                    'transaction_id' => $transaction->id,
                    'transaction_type' => 'student_prepaid_deposit',
                    'date' => now(),
                    'description' => $notes ?: "Prepaid account deposit for student {$student->first_name} {$student->last_name}",
                    'branch_id' => $this->branchId,
                    'user_id' => $this->userId,
                ]);

                // Automatically apply credit to unpaid invoices
                $account->autoApplyCreditToUnpaidInvoices();

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $this->errorCount++;
                Log::error('Prepaid Account Import Error', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

