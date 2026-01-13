<?php

namespace App\Imports;

use App\Models\School\StudentFeeOpeningBalance;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\AcademicYear;
use App\Models\FeeGroup;
use App\Models\GlTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentOpeningBalanceImport implements ToCollection, WithHeadingRow
{
    protected $class;
    protected $stream;
    protected $academicYear;
    protected $companyId;
    protected $branchId;
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    public function __construct(Classe $class, ?Stream $stream, AcademicYear $academicYear, $companyId, $branchId)
    {
        $this->class = $class;
        $this->stream = $stream;
        $this->academicYear = $academicYear;
        $this->companyId = $companyId;
        $this->branchId = $branchId;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            // Log first row to see what we're getting
            if ($rows->isNotEmpty()) {
                Log::info('Student Opening Balance Import - First row sample', [
                    'first_row_keys' => array_keys($rows->first()->toArray()),
                    'first_row_data' => $rows->first()->toArray()
                ]);
            }

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because of header row and 0-based index

                try {
                    // Normalize row keys (WithHeadingRow converts spaces to underscores and lowercases)
                    $rowArray = $row->toArray();
                    
                    // Try multiple possible key formats
                    $admissionNumber = '';
                    if (isset($rowArray['admission_number'])) {
                        $admissionNumber = trim($rowArray['admission_number']);
                    } elseif (isset($rowArray['admission number'])) {
                        $admissionNumber = trim($rowArray['admission number']);
                    } elseif (isset($rowArray['Admission Number'])) {
                        $admissionNumber = trim($rowArray['Admission Number']);
                    } else {
                        // Try to get from first column if key mapping failed
                        $values = array_values($rowArray);
                        $admissionNumber = isset($values[0]) ? trim($values[0]) : '';
                    }
                    
                    if (empty($admissionNumber)) {
                        // Skip empty rows
                        continue;
                    }

                    // Find student
                    $student = Student::where('admission_number', $admissionNumber)
                        ->where('company_id', $this->companyId)
                        ->first();

                    if (!$student) {
                        $this->errors[] = "Row {$rowNumber}: Student with admission number '{$admissionNumber}' not found";
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

                    // Get fee group (try multiple key formats)
                    $feeGroupText = '';
                    if (isset($rowArray['fee_group'])) {
                        $feeGroupText = trim($rowArray['fee_group']);
                    } elseif (isset($rowArray['fee group'])) {
                        $feeGroupText = trim($rowArray['fee group']);
                    } elseif (isset($rowArray['Fee Group'])) {
                        $feeGroupText = trim($rowArray['Fee Group']);
                    } else {
                        $values = array_values($rowArray);
                        $feeGroupText = isset($values[3]) ? trim($values[3]) : ''; // Fee Group is 4th column (index 3)
                    }
                    
                    if (empty($feeGroupText)) {
                        $this->errors[] = "Row {$rowNumber}: Fee group is required";
                        $this->errorCount++;
                        continue;
                    }

                    // Parse fee group (format: "CODE - Name" or just "CODE" or just "Name")
                    $feeGroup = null;
                    if (strpos($feeGroupText, ' - ') !== false) {
                        $parts = explode(' - ', $feeGroupText, 2);
                        $feeGroupCode = trim($parts[0]);
                        $feeGroup = FeeGroup::where('fee_code', $feeGroupCode)
                            ->where('company_id', $this->companyId)
                            ->where('is_active', true)
                            ->first();
                    } else {
                        // Try by code first, then by name
                        $feeGroup = FeeGroup::where('fee_code', $feeGroupText)
                            ->orWhere('name', $feeGroupText)
                            ->where('company_id', $this->companyId)
                            ->where('is_active', true)
                            ->first();
                    }

                    if (!$feeGroup) {
                        $this->errors[] = "Row {$rowNumber}: Fee group '{$feeGroupText}' not found";
                        $this->errorCount++;
                        continue;
                    }

                    if (!$feeGroup->receivable_account_id || !$feeGroup->opening_balance_account_id) {
                        $this->errors[] = "Row {$rowNumber}: Fee group '{$feeGroupText}' does not have required accounts configured";
                        $this->errorCount++;
                        continue;
                    }

                    // Get opening date (try multiple key formats)
                    $openingDate = date('Y-m-d');
                    if (isset($rowArray['opening_date'])) {
                        $openingDate = $rowArray['opening_date'];
                    } elseif (isset($rowArray['opening date'])) {
                        $openingDate = $rowArray['opening date'];
                    } elseif (isset($rowArray['Opening Date'])) {
                        $openingDate = $rowArray['Opening Date'];
                    } else {
                        $values = array_values($rowArray);
                        $openingDate = isset($values[4]) ? $values[4] : date('Y-m-d'); // Opening Date is 5th column (index 4)
                    }
                    
                    if (is_numeric($openingDate)) {
                        // Excel date serial number
                        $openingDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($openingDate)->format('Y-m-d');
                    } else {
                        $openingDate = date('Y-m-d', strtotime($openingDate));
                    }

                    // Get notes (try multiple key formats)
                    $notes = '';
                    if (isset($rowArray['notes'])) {
                        $notes = trim($rowArray['notes']);
                    } elseif (isset($rowArray['Notes'])) {
                        $notes = trim($rowArray['Notes']);
                    } else {
                        $values = array_values($rowArray);
                        $notes = isset($values[5]) ? trim($values[5]) : ''; // Notes is 6th column (index 5)
                    }

                    // Check if opening balance already exists
                    $existing = StudentFeeOpeningBalance::where('student_id', $student->id)
                        ->where('academic_year_id', $this->academicYear->id)
                        ->first();

                    if ($existing) {
                        $this->errors[] = "Row {$rowNumber}: Opening balance already exists for student '{$admissionNumber}' in academic year '{$this->academicYear->year_name}'";
                        $this->errorCount++;
                        continue;
                    }

                    // Get or create LIPISHA control number if LIPISHA is enabled
                    $controlNumber = null;
                    if (\App\Services\LipishaService::isEnabled()) {
                        try {
                            \Log::info('🔍 Attempting to get LIPISHA control number for imported opening balance', [
                                'student_id' => $student->id,
                                'admission_number' => $admissionNumber,
                                'amount' => $amount,
                                'academic_year_id' => $this->academicYear->id
                            ]);
                            
                            $controlNumber = \App\Services\LipishaService::getControlNumberForInvoice(
                                $student,
                                $amount,
                                null, // No period for opening balance
                                $this->academicYear->id,
                                null, // No invoice number for opening balance
                                "Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})"
                            );
                            
                            \Log::info('🔍 LIPISHA control number result for import', [
                                'student_id' => $student->id,
                                'admission_number' => $admissionNumber,
                                'control_number' => $controlNumber,
                                'control_number_type' => gettype($controlNumber),
                                'control_number_empty' => empty($controlNumber)
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('❌ Failed to get LIPISHA control number for imported opening balance', [
                                'student_id' => $student->id,
                                'admission_number' => $admissionNumber,
                                'error' => $e->getMessage()
                            ]);
                            // Continue without control number - opening balance can still be created
                        }
                    }

                    // Create opening balance
                    $openingBalance = StudentFeeOpeningBalance::create([
                        'student_id' => $student->id,
                        'academic_year_id' => $this->academicYear->id,
                        'fee_group_id' => $feeGroup->id,
                        'opening_date' => $openingDate,
                        'amount' => $amount,
                        'paid_amount' => 0,
                        'balance_due' => $amount,
                        'status' => 'posted',
                        'reference' => null,
                        'lipisha_control_number' => $controlNumber,
                        'notes' => $notes,
                        'company_id' => $this->companyId,
                        'branch_id' => $this->branchId,
                        'created_by' => auth()->id(),
                    ]);

                    // Create GL transactions
                    $userId = auth()->id();
                    $description = "Student Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})";

                    // Debit: Receivable Account
                    GlTransaction::create([
                        'chart_account_id' => $feeGroup->receivable_account_id,
                        'customer_id' => null,
                        'supplier_id' => null,
                        'amount' => $amount,
                        'nature' => 'debit',
                        'transaction_id' => $openingBalance->id,
                        'transaction_type' => 'student_fee_opening_balance',
                        'date' => $openingDate,
                        'description' => $description,
                        'branch_id' => $this->branchId,
                        'user_id' => $userId,
                    ]);

                    // Credit: Opening Balance Account
                    GlTransaction::create([
                        'chart_account_id' => $feeGroup->opening_balance_account_id,
                        'customer_id' => null,
                        'supplier_id' => null,
                        'amount' => $amount,
                        'nature' => 'credit',
                        'transaction_id' => $openingBalance->id,
                        'transaction_type' => 'student_fee_opening_balance',
                        'date' => $openingDate,
                        'description' => $description,
                        'branch_id' => $this->branchId,
                        'user_id' => $userId,
                    ]);

                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $this->errorCount++;
                    Log::error('Student Opening Balance Import Error', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = "Import failed: " . $e->getMessage();
            $this->errorCount++;
            Log::error('Student Opening Balance Import Transaction Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

