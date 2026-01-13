<?php

namespace App\Imports;

use App\Models\School\Student;
use App\Models\School\AssignmentSubmission;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AssignmentSubmissionsImport implements ToArray, WithHeadingRow
{
    protected $assignment;
    protected $companyId;
    protected $branchId;
    protected $gradeScale;
    protected $errors = [];
    protected $successCount = 0;

    public function __construct($assignment, $companyId, $branchId, $gradeScale)
    {
        $this->assignment = $assignment;
        $this->companyId = $companyId;
        $this->branchId = $branchId;
        $this->gradeScale = $gradeScale;
    }

    public function array(array $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because Excel rows start at 1 and we skip header

            try {
                // Get admission number (handle different possible column names)
                $admissionNumber = $this->getValue($row, ['admission_number', 'admission number', 'admission_no']);
                
                if (empty($admissionNumber)) {
                    $this->errors[] = "Row {$rowNumber}: Admission Number is required";
                    continue;
                }

                // Find student by admission number
                $student = Student::where('company_id', $this->companyId)
                    ->where(function ($query) {
                        $query->where('branch_id', $this->branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->where('admission_number', $admissionNumber)
                    ->first();

                if (!$student) {
                    $this->errors[] = "Row {$rowNumber}: Student with admission number '{$admissionNumber}' not found";
                    continue;
                }

                // Get marks and comments
                $marksObtained = $this->getValue($row, ['marks_obtained', 'marks obtained', 'marks']);
                $comments = $this->getValue($row, ['comments', 'comment', 'teacher_comments', 'teacher comments']);

                // Skip if no marks provided
                if (empty($marksObtained) && $marksObtained !== '0' && $marksObtained !== 0) {
                    continue; // Skip rows without marks
                }

                // Validate marks
                $marksObtained = is_numeric($marksObtained) ? (float)$marksObtained : null;
                if ($marksObtained === null) {
                    $this->errors[] = "Row {$rowNumber}: Invalid marks value for student '{$admissionNumber}'";
                    continue;
                }

                if ($marksObtained < 0 || ($this->assignment->total_marks && $marksObtained > $this->assignment->total_marks)) {
                    $this->errors[] = "Row {$rowNumber}: Marks must be between 0 and {$this->assignment->total_marks} for student '{$admissionNumber}'";
                    continue;
                }

                // Calculate percentage
                $percentage = null;
                if ($this->assignment->total_marks && $marksObtained >= 0) {
                    $percentage = ($marksObtained / $this->assignment->total_marks) * 100;
                }

                // Determine grade and remarks
                $grade = null;
                $remarks = null;
                if ($percentage !== null) {
                    if ($this->gradeScale) {
                        $gradeObj = $this->gradeScale->getGradeForMark($percentage);
                        if ($gradeObj) {
                            $grade = $gradeObj->grade_letter;
                            $remarks = $gradeObj->remarks;
                        }
                    } else {
                        // Fallback to default grading
                        if ($percentage >= 90) {
                            $grade = 'A';
                            $remarks = 'EXCELLENT';
                        } elseif ($percentage >= 80) {
                            $grade = 'B';
                            $remarks = 'VERY GOOD';
                        } elseif ($percentage >= 70) {
                            $grade = 'C';
                            $remarks = 'AVERAGE';
                        } elseif ($percentage >= 60) {
                            $grade = 'D';
                            $remarks = 'BELOW AVERAGE';
                        } else {
                            $grade = 'E';
                            $remarks = 'UNSATISFACTORY';
                        }
                    }
                }

                // Find or create submission
                $submission = AssignmentSubmission::where('assignment_id', $this->assignment->id)
                    ->where('student_id', $student->id)
                    ->orderBy('attempt_number', 'asc')
                    ->first();

                $submissionData = [
                    'assignment_id' => $this->assignment->id,
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'stream_id' => $student->stream_id,
                    'marks_obtained' => $marksObtained,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'remarks' => $remarks,
                    'teacher_comments' => $comments ?: null,
                    'status' => 'marked',
                    'marked_by' => auth()->id(),
                    'marked_at' => now(),
                    'company_id' => $this->companyId,
                    'branch_id' => $this->branchId,
                ];

                if ($submission) {
                    $submission->update($submissionData);
                } else {
                    $submissionData['attempt_number'] = 1;
                    AssignmentSubmission::create($submissionData);
                }

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    protected function getValue($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Try exact match first
            if (isset($row[$key])) {
                return $row[$key];
            }
            
            // Try case-insensitive match
            foreach ($row as $rowKey => $value) {
                if (strtolower(trim($rowKey)) === strtolower(trim($key))) {
                    return $value;
                }
            }
        }
        
        return null;
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

