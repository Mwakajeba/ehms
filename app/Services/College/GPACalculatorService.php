<?php

namespace App\Services\College;

use App\Models\College\CourseResult;
use App\Models\College\Student;
use Illuminate\Support\Collection;

class GPACalculatorService
{
    /**
     * University Grading Scale Configuration
     * Based on 5-point GPA system
     * 
     * Marks (%)    | Grade | Grade Point | Remark
     * -------------|-------|-------------|------------------
     * 70 – 100     | A     | 5           | Excellent
     * 60 – 69      | B     | 4           | Very Good
     * 50 – 59      | C     | 3           | Good
     * 40 – 49      | D     | 2           | Pass
     * 35 – 39      | E     | 1           | Marginal Pass
     * 0 – 34       | F     | 0           | Fail
     */
    protected array $gradingScale = [
        ['min' => 70, 'max' => 100, 'grade' => 'A', 'gpa' => 5.0, 'remark' => 'Excellent', 'pass' => true],
        ['min' => 60, 'max' => 69.99, 'grade' => 'B', 'gpa' => 4.0, 'remark' => 'Very Good', 'pass' => true],
        ['min' => 50, 'max' => 59.99, 'grade' => 'C', 'gpa' => 3.0, 'remark' => 'Good', 'pass' => true],
        ['min' => 40, 'max' => 49.99, 'grade' => 'D', 'gpa' => 2.0, 'remark' => 'Pass', 'pass' => true],
        ['min' => 35, 'max' => 39.99, 'grade' => 'E', 'gpa' => 1.0, 'remark' => 'Marginal Pass', 'pass' => true],
        ['min' => 0, 'max' => 34.99, 'grade' => 'F', 'gpa' => 0.0, 'remark' => 'Fail', 'pass' => false],
    ];

    /**
     * GPA Classification Thresholds
     * 
     * GPA Range   | Classification
     * ------------|---------------------------
     * 4.50 – 5.00 | First Class Honours
     * 3.50 – 4.49 | Second Class Upper
     * 2.40 – 3.49 | Second Class Lower
     * 1.50 – 2.39 | Third Class
     * 1.00 – 1.49 | Pass
     * Below 1.00  | Fail
     */
    protected array $classifications = [
        ['min' => 4.50, 'max' => 5.00, 'class' => 'First Class Honours', 'code' => '1st'],
        ['min' => 3.50, 'max' => 4.49, 'class' => 'Second Class Upper', 'code' => '2.1'],
        ['min' => 2.40, 'max' => 3.49, 'class' => 'Second Class Lower', 'code' => '2.2'],
        ['min' => 1.50, 'max' => 2.39, 'class' => 'Third Class', 'code' => '3rd'],
        ['min' => 1.00, 'max' => 1.49, 'class' => 'Pass', 'code' => 'Pass'],
        ['min' => 0, 'max' => 0.99, 'class' => 'Fail', 'code' => 'Fail'],
    ];

    /**
     * Get grade information for given marks
     */
    public function getGradeForMarks(float $marks): array
    {
        $normalizedMarks = min(max($marks, 0), 100);
        
        foreach ($this->gradingScale as $scale) {
            if ($normalizedMarks >= $scale['min'] && $normalizedMarks <= $scale['max']) {
                return $scale;
            }
        }

        return ['grade' => 'F', 'gpa' => 0.0, 'remark' => 'Fail', 'pass' => false];
    }

    /**
     * Calculate GPA for a collection of course results
     * 
     * Formula: GPA = Σ(Credit Hours × Grade Points) ÷ Σ(Credit Hours)
     * 
     * @param Collection $results Collection of CourseResult models
     * @return array ['gpa' => float, 'total_credits' => int, 'total_quality_points' => float, 'classification' => string]
     */
    public function calculateGPA(Collection $results): array
    {
        if ($results->isEmpty()) {
            return [
                'gpa' => 0.00,
                'total_credits' => 0,
                'total_quality_points' => 0.00,
                'classification' => 'N/A',
                'classification_code' => 'N/A',
                'courses_count' => 0,
                'passed_count' => 0,
                'failed_count' => 0,
            ];
        }

        $totalQualityPoints = 0.0;
        $totalCredits = 0;
        $passedCount = 0;
        $failedCount = 0;

        foreach ($results as $result) {
            $credits = (float) ($result->credit_hours ?? 0);
            $gradePoints = (float) ($result->gpa_points ?? 0);
            
            // Quality Points = Credit Hours × Grade Points
            $qualityPoints = $credits * $gradePoints;
            
            $totalQualityPoints += $qualityPoints;
            $totalCredits += $credits;

            // Count passed/failed
            if ($result->course_status === 'passed' || $gradePoints >= 1.0) {
                $passedCount++;
            } else {
                $failedCount++;
            }
        }

        // GPA = Total Quality Points ÷ Total Credits
        $gpa = $totalCredits > 0 ? round($totalQualityPoints / $totalCredits, 2) : 0.00;
        
        $classification = $this->getClassification($gpa);

        return [
            'gpa' => $gpa,
            'total_credits' => $totalCredits,
            'total_quality_points' => round($totalQualityPoints, 2),
            'classification' => $classification['class'],
            'classification_code' => $classification['code'],
            'courses_count' => $results->count(),
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
        ];
    }

    /**
     * Calculate Semester GPA for a student
     */
    public function calculateSemesterGPA(int $studentId, int $academicYearId, int $semesterId): array
    {
        $results = CourseResult::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->whereIn('result_status', ['published', 'approved'])
            ->get();

        return $this->calculateGPA($results);
    }

    /**
     * Calculate Cumulative GPA (CGPA) for a student
     * 
     * CGPA = Overall Quality Points ÷ Overall Credit Hours
     * (Across all semesters)
     */
    public function calculateCGPA(int $studentId): array
    {
        $results = CourseResult::where('student_id', $studentId)
            ->whereIn('result_status', ['published', 'approved'])
            ->get();

        $gpaData = $this->calculateGPA($results);
        
        // Add CGPA-specific fields
        $gpaData['type'] = 'CGPA';
        
        return $gpaData;
    }

    /**
     * Calculate yearly GPA
     */
    public function calculateYearlyGPA(int $studentId, int $academicYearId): array
    {
        $results = CourseResult::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('result_status', ['published', 'approved'])
            ->get();

        return $this->calculateGPA($results);
    }

    /**
     * Get comprehensive academic summary for a student
     */
    public function getStudentAcademicSummary(int $studentId): array
    {
        $student = Student::with('program')->findOrFail($studentId);
        
        $results = CourseResult::with(['course', 'academicYear', 'semester'])
            ->where('student_id', $studentId)
            ->whereIn('result_status', ['published', 'approved'])
            ->orderBy('academic_year_id')
            ->orderBy('semester_id')
            ->get();

        // Group by academic year and semester
        $grouped = $results->groupBy(function ($result) {
            return $result->academic_year_id . '_' . $result->semester_id;
        });

        $semesterSummaries = [];
        $yearlySummaries = [];
        $totalQualityPoints = 0.0;
        $totalCredits = 0;

        foreach ($grouped as $key => $periodResults) {
            $firstResult = $periodResults->first();
            $semesterGPA = $this->calculateGPA($periodResults);
            
            $semesterSummaries[$key] = [
                'academic_year_id' => $firstResult->academic_year_id,
                'academic_year' => $firstResult->academicYear->name ?? 'N/A',
                'semester_id' => $firstResult->semester_id,
                'semester' => $firstResult->semester->name ?? 'N/A',
                'results' => $periodResults,
                'gpa' => $semesterGPA['gpa'],
                'classification' => $semesterGPA['classification'],
                'total_credits' => $semesterGPA['total_credits'],
                'total_quality_points' => $semesterGPA['total_quality_points'],
                'courses_count' => $semesterGPA['courses_count'],
                'passed_count' => $semesterGPA['passed_count'],
                'failed_count' => $semesterGPA['failed_count'],
            ];

            $totalQualityPoints += $semesterGPA['total_quality_points'];
            $totalCredits += $semesterGPA['total_credits'];

            // Group by year for yearly summaries
            $yearId = $firstResult->academic_year_id;
            if (!isset($yearlySummaries[$yearId])) {
                $yearlySummaries[$yearId] = [
                    'academic_year' => $firstResult->academicYear->name ?? 'N/A',
                    'total_quality_points' => 0,
                    'total_credits' => 0,
                ];
            }
            $yearlySummaries[$yearId]['total_quality_points'] += $semesterGPA['total_quality_points'];
            $yearlySummaries[$yearId]['total_credits'] += $semesterGPA['total_credits'];
        }

        // Calculate yearly GPAs
        foreach ($yearlySummaries as $yearId => &$yearData) {
            $yearData['gpa'] = $yearData['total_credits'] > 0 
                ? round($yearData['total_quality_points'] / $yearData['total_credits'], 2) 
                : 0.00;
            $yearData['classification'] = $this->getClassification($yearData['gpa'])['class'];
        }

        // Calculate CGPA
        $cgpa = $totalCredits > 0 ? round($totalQualityPoints / $totalCredits, 2) : 0.00;
        $finalClassification = $this->getClassification($cgpa);

        return [
            'student' => $student,
            'semesters' => $semesterSummaries,
            'yearly' => $yearlySummaries,
            'cgpa' => $cgpa,
            'total_credits' => $totalCredits,
            'total_quality_points' => round($totalQualityPoints, 2),
            'classification' => $finalClassification['class'],
            'classification_code' => $finalClassification['code'],
            'total_courses' => $results->count(),
            'passed_courses' => $results->where('course_status', 'passed')->count(),
            'failed_courses' => $results->where('course_status', 'failed')->count(),
        ];
    }

    /**
     * Get classification based on GPA
     */
    public function getClassification(float $gpa): array
    {
        foreach ($this->classifications as $class) {
            if ($gpa >= $class['min'] && $gpa <= $class['max']) {
                return $class;
            }
        }

        return ['class' => 'Fail', 'code' => 'Fail', 'min' => 0, 'max' => 0];
    }

    /**
     * Get the full grading scale
     */
    public function getGradingScale(): array
    {
        return $this->gradingScale;
    }

    /**
     * Get the classification thresholds
     */
    public function getClassifications(): array
    {
        return $this->classifications;
    }

    /**
     * Calculate weighted score for a course
     * CA Weight: 40%, Exam Weight: 60%
     */
    public function calculateWeightedScore(float $caScore, float $examScore, float $caWeight = 40, float $examWeight = 60): float
    {
        // Normalize weights to ensure they sum to 100
        $totalWeight = $caWeight + $examWeight;
        $normalizedCaWeight = ($caWeight / $totalWeight) * 100;
        $normalizedExamWeight = ($examWeight / $totalWeight) * 100;

        // Calculate weighted total
        $caContribution = ($caScore / 100) * $normalizedCaWeight;
        $examContribution = ($examScore / 100) * $normalizedExamWeight;

        return round($caContribution + $examContribution, 2);
    }

    /**
     * Check if a student can graduate based on CGPA
     */
    public function canGraduate(int $studentId, float $minimumCGPA = 1.0): array
    {
        $summary = $this->getStudentAcademicSummary($studentId);
        
        $canGraduate = $summary['cgpa'] >= $minimumCGPA && $summary['failed_courses'] === 0;
        
        return [
            'can_graduate' => $canGraduate,
            'cgpa' => $summary['cgpa'],
            'classification' => $summary['classification'],
            'failed_courses' => $summary['failed_courses'],
            'reason' => $canGraduate 
                ? 'Student meets all graduation requirements.' 
                : ($summary['cgpa'] < $minimumCGPA 
                    ? "CGPA ({$summary['cgpa']}) is below minimum requirement ({$minimumCGPA})." 
                    : "Student has {$summary['failed_courses']} failed course(s) to clear."),
        ];
    }

    /**
     * Get students by classification
     */
    public function getStudentsByClassification(string $classCode, ?int $programId = null): Collection
    {
        $query = Student::with('program');
        
        if ($programId) {
            $query->where('program_id', $programId);
        }

        $students = $query->get();
        $classifiedStudents = collect();

        foreach ($students as $student) {
            $summary = $this->getStudentAcademicSummary($student->id);
            if ($summary['classification_code'] === $classCode) {
                $student->cgpa = $summary['cgpa'];
                $student->classification = $summary['classification'];
                $classifiedStudents->push($student);
            }
        }

        return $classifiedStudents->sortByDesc('cgpa');
    }

    /**
     * Generate GPA report data for a batch of students
     */
    public function generateBatchGPAReport(Collection $studentIds, ?int $academicYearId = null, ?int $semesterId = null): array
    {
        $reports = [];

        foreach ($studentIds as $studentId) {
            if ($academicYearId && $semesterId) {
                $reports[$studentId] = $this->calculateSemesterGPA($studentId, $academicYearId, $semesterId);
            } elseif ($academicYearId) {
                $reports[$studentId] = $this->calculateYearlyGPA($studentId, $academicYearId);
            } else {
                $reports[$studentId] = $this->calculateCGPA($studentId);
            }
        }

        // Sort by GPA descending
        uasort($reports, fn($a, $b) => $b['gpa'] <=> $a['gpa']);

        // Add rankings
        $rank = 1;
        foreach ($reports as $studentId => &$report) {
            $report['rank'] = $rank++;
        }

        return $reports;
    }
}
