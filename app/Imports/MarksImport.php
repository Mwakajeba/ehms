<?php

namespace App\Imports;

use App\Models\SchoolExamMark;
use App\Models\ExamClassAssignment;
use App\Models\School\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
// Remove WithHeadingRow since we need to handle headers manually
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarksImport implements ToCollection
{
    protected $examTypeId;
    protected $classId;
    protected $streamId;
    protected $academicYearId;
    protected $companyId;
    protected $branchId;
    protected $userId;
    protected $errors = [];
    protected $successCount = 0;

    public function __construct($examTypeId, $classId = null, $streamId = null, $academicYearId = null, $companyId = null, $branchId = null, $userId = null)
    {
        $this->examTypeId = $examTypeId;
        $this->classId = $classId;
        $this->streamId = $streamId;
        $this->academicYearId = $academicYearId;
        $this->companyId = $companyId;
        $this->branchId = $branchId;
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();

            // Skip the first row (info header) and get headers from second row
            $rowsArray = $rows->toArray();

            Log::info('Marks Import Debug', [
                'total_rows' => count($rowsArray),
                'exam_type_id' => $this->examTypeId,
                'class_id' => $this->classId,
                'academic_year_id' => $this->academicYearId,
                'company_id' => $this->companyId,
                'branch_id' => $this->branchId
            ]);

            if (count($rowsArray) < 2) {
                $this->errors[] = 'Excel file must have at least 2 rows (headers and data)';
                return;
            }

            // Second row contains the actual headers
            $headers = $rowsArray[1];

            Log::info('Headers found', ['headers' => $headers]);

            // Convert headers to snake_case for easier processing
            $headerMap = [];
            foreach ($headers as $index => $header) {
                $header = trim($header);
                if (strtolower($header) === 'admission number') {
                    $headerMap[$index] = 'admission_number';
                } elseif (strtolower($header) === 'student name') {
                    $headerMap[$index] = 'student_name';
                } elseif (strtolower($header) === 'class') {
                    $headerMap[$index] = 'class';
                } elseif (strtolower($header) === 'stream') {
                    $headerMap[$index] = 'stream';
                } else {
                    // Subject names - keep as is for subject matching
                    $headerMap[$index] = $header;
                }
            }

            Log::info('Header map created', ['header_map' => $headerMap]);

            // Process data rows (skip first 2 rows: info header and column headers)
            for ($i = 2; $i < count($rowsArray); $i++) {
                $row = $rowsArray[$i];
                Log::info('Processing row ' . $i, ['row_data' => $row]);

                // Convert row to associative array using headers
                $rowData = [];
                foreach ($headerMap as $index => $headerKey) {
                    $rowData[$headerKey] = $row[$index] ?? '';
                }

                Log::info('Converted row data', ['row_data' => $rowData]);
                $this->processRow($rowData);
            }

            DB::commit();

            Log::info('Import completed', [
                'success_count' => $this->successCount,
                'errors' => $this->errors
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Marks Import Error: ' . $e->getMessage(), [
                'exam_type_id' => $this->examTypeId,
                'class_id' => $this->classId,
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString()
            ]);
            $this->errors[] = 'Import failed: ' . $e->getMessage();
        }
    }

    protected function processRow($row)
    {
        try {
            // Extract student information
            $admissionNumber = trim($row['admission_number'] ?? '');
            $studentName = trim($row['student_name'] ?? '');

            Log::info('Processing student row', [
                'admission_number' => $admissionNumber,
                'student_name' => $studentName
            ]);

            if (empty($admissionNumber) && empty($studentName)) {
                $this->errors[] = 'Row skipped: Missing admission number and student name';
                return;
            }

            // Find student by admission number first, then by name if needed
            $student = null;
            if (!empty($admissionNumber)) {
                $student = Student::where('admission_number', $admissionNumber)
                    ->where('company_id', $this->companyId)
                    ->where(function ($query) {
                        $query->where('branch_id', $this->branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->first();

                Log::info('Student search by admission number', [
                    'admission_number' => $admissionNumber,
                    'found' => $student ? 'yes' : 'no',
                    'student_id' => $student ? $student->id : null
                ]);
            }

            if (!$student && !empty($studentName)) {
                // Try to find by name (split name and search)
                $nameParts = explode(' ', $studentName, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                $student = Student::where('first_name', 'LIKE', $firstName)
                    ->where('last_name', 'LIKE', $lastName)
                    ->where('company_id', $this->companyId)
                    ->where(function ($query) {
                        $query->where('branch_id', $this->branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->first();

                Log::info('Student search by name', [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'found' => $student ? 'yes' : 'no',
                    'student_id' => $student ? $student->id : null
                ]);
            }

            if (!$student) {
                $this->errors[] = "Student not found: {$admissionNumber} - {$studentName}";
                return;
            }

            // Get subjects from the row data (all keys except the fixed ones)
            $subjectColumns = collect($row)->keys()->filter(function ($key) {
                return !in_array($key, ['admission_number', 'student_name', 'class', 'stream']);
            });

            Log::info('Subject columns found', ['subjects' => $subjectColumns->toArray()]);

            foreach ($subjectColumns as $subjectName) {
                $mark = trim($row[$subjectName] ?? '');

                Log::info('Processing subject mark', [
                    'subject_name' => $subjectName,
                    'mark' => $mark
                ]);

                // Skip empty marks
                if ($mark === '' || $mark === null) {
                    Log::info('Skipping empty mark for subject', ['subject' => $subjectName]);
                    continue;
                }

                // Validate mark is numeric
                if (!is_numeric($mark)) {
                    $this->errors[] = "Invalid mark for {$studentName} - {$subjectName}: {$mark}";
                    continue;
                }

                $markValue = (float) $mark;

                // Validate mark range (0-100)
                if ($markValue < 0 || $markValue > 100) {
                    $this->errors[] = "Mark out of range for {$studentName} - {$subjectName}: {$markValue}";
                    continue;
                }

                // Find subject by name/short_name
                $subject = DB::table('subjects')
                    ->where(function ($query) use ($subjectName) {
                        $query->where('name', $subjectName)
                              ->orWhere('short_name', $subjectName);
                    })
                    ->whereNull('deleted_at')
                    ->first();

                Log::info('Subject search result', [
                    'subject_name_searched' => $subjectName,
                    'subject_found' => $subject ? 'yes' : 'no',
                    'subject_id' => $subject ? $subject->id : null,
                    'subject_name' => $subject ? $subject->name : null,
                    'subject_short_name' => $subject ? $subject->short_name : null,
                    'subject_deleted' => $subject && $subject->deleted_at ? 'yes' : 'no'
                ]);

                Log::info('Subject search result', [
                    'subject_name' => $subjectName,
                    'found' => $subject ? 'yes' : 'no',
                    'subject_id' => $subject ? $subject->id : null
                ]);

                if (!$subject) {
                    $this->errors[] = "Subject not found: {$subjectName}";
                    continue;
                }

                // Find exam class assignment for this subject, class, and exam type
                $assignment = ExamClassAssignment::where('exam_type_id', $this->examTypeId)
                    ->where('academic_year_id', $this->academicYearId)
                    ->where('subject_id', $subject->id)
                    ->where('class_id', $student->class_id)
                    ->where('company_id', $this->companyId)
                    ->where(function ($query) {
                        $query->where('branch_id', $this->branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->first();

                Log::info('Exam assignment search', [
                    'exam_type_id' => $this->examTypeId,
                    'academic_year_id' => $this->academicYearId,
                    'subject_id' => $subject->id,
                    'class_id' => $student->class_id,
                    'company_id' => $this->companyId,
                    'branch_id' => $this->branchId,
                    'assignment_found' => $assignment ? 'yes' : 'no',
                    'assignment_id' => $assignment ? $assignment->id : null
                ]);

                Log::info('Exam assignment search', [
                    'exam_type_id' => $this->examTypeId,
                    'academic_year_id' => $this->academicYearId,
                    'subject_id' => $subject->id,
                    'class_id' => $student->class_id,
                    'found' => $assignment ? 'yes' : 'no',
                    'assignment_id' => $assignment ? $assignment->id : null
                ]);

                if (!$assignment) {
                    $this->errors[] = "No exam assignment found for {$studentName} - {$subjectName}";
                    continue;
                }

                // Check if student is registered for this exam
                $registration = \App\Models\SchoolExamRegistration::where('exam_class_assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->where('company_id', $this->companyId)
                    ->where(function ($query) {
                        $query->where('branch_id', $this->branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->first();

                // Only save marks if student is registered
                if (!$registration || $registration->status !== 'registered') {
                    // Skip importing marks for non-registered students
                    // If there's an existing mark, delete it
                    SchoolExamMark::where('exam_class_assignment_id', $assignment->id)
                        ->where('student_id', $student->id)
                        ->delete();
                    
                    $statusText = $registration ? $registration->status : 'not registered';
                    Log::info('Mark skipped - student not registered', [
                        'student_id' => $student->id,
                        'assignment_id' => $assignment->id,
                        'status' => $statusText
                    ]);
                    continue;
                }

                // Save or update the mark
                SchoolExamMark::updateOrCreate(
                    [
                        'exam_class_assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'marks_obtained' => $markValue,
                        'max_marks' => 100, // Default max marks
                        'company_id' => $this->companyId,
                        'branch_id' => $this->branchId,
                        'created_by' => $this->userId,
                        'updated_by' => $this->userId,
                    ]
                );

                Log::info('Mark saved successfully', [
                    'student_id' => $student->id,
                    'assignment_id' => $assignment->id,
                    'mark' => $markValue
                ]);

                $this->successCount++;
            }

        } catch (\Exception $e) {
            $this->errors[] = 'Error processing row: ' . $e->getMessage();
            Log::error('Row processing error: ' . $e->getMessage(), [
                'row' => $row,
                'exam_type_id' => $this->examTypeId,
                'user_id' => $this->userId
            ]);
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}