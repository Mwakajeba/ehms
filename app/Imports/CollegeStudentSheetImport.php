<?php

namespace App\Imports;

use App\Models\College\Student;
use App\Models\College\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class CollegeStudentSheetImport implements ToCollection, WithHeadingRow
{
    private $program;
    private $updateExisting;
    private $companyId;
    private $branchId;
    private $enrollmentYear;
    private $admissionLevel;
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];
    private $duplicates = [];
    private $previewData = [];

    public function __construct(Program $program, $updateExisting = false, $companyId = null, $branchId = null, $enrollmentYear = null, $admissionLevel = null)
    {
        $this->program = $program;
        $this->updateExisting = $updateExisting;
        $this->companyId = $companyId;
        $this->branchId = $branchId;
        $this->enrollmentYear = $enrollmentYear;
        $this->admissionLevel = $admissionLevel;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because Excel rows start at 1 and we have header row

            try {
                // Convert row to array and normalize column names
                $rowData = is_array($row) ? $row : $row->toArray();
                $normalizedRow = $this->normalizeRowData($rowData);
                
                // Validate row data (only essential fields)
                $validator = Validator::make($normalizedRow, [
                    'student_number' => 'required|string|max:255',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'gender' => 'required|in:male,female,other',
                ]);

                if ($validator->fails()) {
                    $errorMessages = [];
                    foreach ($validator->errors()->messages() as $field => $messages) {
                        foreach ($messages as $message) {
                            $errorMessages[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $message;
                        }
                    }
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'field' => implode(', ', array_keys($validator->errors()->messages())),
                        'message' => implode('; ', $errorMessages)
                    ];
                    $this->errorCount++;
                    continue;
                }

                // Check for duplicates
                $existingStudent = Student::where('company_id', $this->companyId)
                    ->where('student_number', $normalizedRow['student_number'])
                    ->first();

                if ($existingStudent) {
                    $this->duplicates[] = [
                        'row' => $rowNumber,
                        'student_number' => $normalizedRow['student_number'],
                        'existing_student' => $existingStudent->first_name . ' ' . $existingStudent->last_name
                    ];
                    continue;
                }

                // Check for email duplicates if email is provided
                if (!empty($normalizedRow['email'])) {
                    $existingEmail = Student::where('company_id', $this->companyId)
                        ->where('email', $normalizedRow['email'])
                        ->first();

                    if ($existingEmail) {
                        $this->duplicates[] = [
                            'row' => $rowNumber,
                            'field' => 'email',
                            'value' => $normalizedRow['email'],
                            'existing_student' => $existingEmail->first_name . ' ' . $existingEmail->last_name
                        ];
                        continue;
                    }
                }

                // For preview mode, collect valid data instead of creating records
                if ($this->updateExisting) {
                    $this->previewData[] = [
                        'row' => $rowNumber,
                        'student_number' => $normalizedRow['student_number'],
                        'name' => $normalizedRow['first_name'] . ' ' . $normalizedRow['last_name'],
                        'gender' => ucfirst($normalizedRow['gender']),
                        'enrollment_year' => $this->enrollmentYear,
                        'admission_level' => $this->admissionLevel,
                        'status' => 'active'
                    ];
                } else {
                    // Create student record with automatic values
                    Student::create([
                        'student_number' => $normalizedRow['student_number'],
                        'first_name' => $normalizedRow['first_name'],
                        'last_name' => $normalizedRow['last_name'],
                        'email' => null, // Not provided in simplified import
                        'password' => \Illuminate\Support\Facades\Hash::make(strtolower($normalizedRow['first_name'])),
                        'phone' => null, // Not provided in simplified import
                        'date_of_birth' => null, // Not provided in simplified import
                        'gender' => $normalizedRow['gender'],
                        'program_id' => $this->program->id,
                        'admission_level' => $this->admissionLevel,
                        'enrollment_year' => $this->enrollmentYear,
                        'status' => 'active', // Always active for simplified import
                        'company_id' => $this->companyId,
                        'branch_id' => $this->branchId,
                        'admission_date' => now(),
                        'permanent_address' => 'Not provided', // Default value for import
                    ]);

                    $this->successCount++;
                }

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => ['Error processing row: ' . $e->getMessage()]
                ];
                $this->errorCount++;
            }
        }
    }

    private function normalizeRowData($row)
    {
        $normalized = [];
        
        // Map of possible column names to standard names
        $columnMappings = [
            'student_number' => ['student_number', 'student no', 'student_no', 'student id', 'student_id', 'id', 'student number'],
            'first_name' => ['first_name', 'first name', 'firstname', 'fname'],
            'last_name' => ['last_name', 'last name', 'lastname', 'lname', 'surname'],
            'gender' => ['gender', 'sex']
        ];
        
        // Normalize column names to lowercase and remove extra spaces and special characters
        $normalizedKeys = [];
        foreach (array_keys($row) as $key) {
            $normalizedKeys[$key] = strtolower(preg_replace('/[^a-z0-9]/', '', $key));
        }
        
        // Map columns to standard names
        foreach ($columnMappings as $standardName => $possibleNames) {
            foreach ($possibleNames as $possibleName) {
                $normalizedPossible = strtolower(preg_replace('/[^a-z0-9]/', '', $possibleName));
                if (isset($normalizedKeys[$possibleName]) || in_array($normalizedPossible, $normalizedKeys)) {
                    $originalKey = array_search($normalizedPossible, $normalizedKeys);
                    if ($originalKey !== false) {
                        $value = $row[$originalKey];
                        // Ensure phone is treated as string
                        if ($standardName === 'phone' && $value !== null) {
                            $value = (string) $value;
                        }
                        // Ensure enrollment_year is treated as integer
                        if ($standardName === 'enrollment_year' && $value !== null) {
                            $value = (int) $value;
                        }
                        $normalized[$standardName] = $value;
                        break;
                    }
                }
            }
        }
        
        // Also try direct key matching for exact matches
        foreach ($columnMappings as $standardName => $possibleNames) {
            foreach ($possibleNames as $possibleName) {
                if (isset($row[$possibleName])) {
                    $value = $row[$possibleName];
                    // Ensure phone is treated as string
                    if ($standardName === 'phone' && $value !== null) {
                        $value = (string) $value;
                    }
                    // Ensure enrollment_year is treated as integer
                    if ($standardName === 'enrollment_year' && $value !== null) {
                        $value = (int) $value;
                    }
                    $normalized[$standardName] = $value;
                    break;
                }
            }
        }
        
        // Normalize gender values
        if (isset($normalized['gender'])) {
            $normalized['gender'] = strtolower($normalized['gender']);
        }
        
        return $normalized;
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

    public function getDuplicates()
    {
        return $this->duplicates;
    }

    public function getPreviewData()
    {
        return $this->previewData;
    }
}