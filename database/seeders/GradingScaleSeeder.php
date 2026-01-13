<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College\GradingScale;
use App\Models\College\GradingScaleItem;
use App\Models\Company;

class GradingScaleSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('No company found! Please create a company first.');
            return;
        }

        // Create the grading scale
        $gradingScale = GradingScale::create([
            'name' => 'Standard University Grading Scale',
            'description' => 'Default grading scale for all programs (A-F system with 5.0 GPA scale)',
            'is_active' => true,
            'company_id' => $company->id,
            'branch_id' => $company->branches->first()?->id,
        ]);

        // Create grading scale items
        $grades = [
            [
                'min_marks' => 80,
                'max_marks' => 100,
                'grade' => 'A',
                'remark' => 'Excellent',
                'gpa_points' => 5.00,
                'pass_status' => 'pass',
            ],
            [
                'min_marks' => 70,
                'max_marks' => 79,
                'grade' => 'B+',
                'remark' => 'Very Good',
                'gpa_points' => 4.00,
                'pass_status' => 'pass',
            ],
            [
                'min_marks' => 60,
                'max_marks' => 69,
                'grade' => 'B',
                'remark' => 'Good',
                'gpa_points' => 3.00,
                'pass_status' => 'pass',
            ],
            [
                'min_marks' => 50,
                'max_marks' => 59,
                'grade' => 'C',
                'remark' => 'Average',
                'gpa_points' => 2.00,
                'pass_status' => 'pass',
            ],
            [
                'min_marks' => 40,
                'max_marks' => 49,
                'grade' => 'D',
                'remark' => 'Pass',
                'gpa_points' => 1.00,
                'pass_status' => 'pass',
            ],
            [
                'min_marks' => 0,
                'max_marks' => 39,
                'grade' => 'F',
                'remark' => 'Fail',
                'gpa_points' => 0.00,
                'pass_status' => 'fail',
            ],
        ];

        foreach ($grades as $grade) {
            GradingScaleItem::create([
                'grading_scale_id' => $gradingScale->id,
                ...$grade,
            ]);
        }

        $this->command->info('Grading scale seeded successfully!');
        $this->command->info('Grades: A(80-100), B+(70-79), B(60-69), C(50-59), D(40-49), F(0-39)');
    }
}
