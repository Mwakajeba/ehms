<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseRegistrationAndAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = 1;
        $companyId = 1;
        
        // Get required IDs
        $academicYearId = DB::table('college_academic_years')->value('id');
        $semesterId = DB::table('college_semesters')->value('id');
        $courses = DB::table('courses')->select('id', 'program_id')->take(5)->get();
        $students = DB::table('college_students')->pluck('id')->take(10)->toArray();
        $assessmentTypes = DB::table('assessment_types')->pluck('id')->toArray();

        if (!$academicYearId || !$semesterId || $courses->isEmpty() || empty($students) || empty($assessmentTypes)) {
            $this->command->error('Missing required data. Please seed academic years, semesters, courses, students, and assessment types first.');
            return;
        }

        // Create Course Registrations - register students for courses (skip if already exists)
        $existingRegistrations = DB::table('course_registrations')->count();
        
        if ($existingRegistrations > 0) {
            $this->command->info('Course registrations already exist (' . $existingRegistrations . ' records). Skipping...');
        } else {
            $registrations = [];
            foreach ($courses as $course) {
                foreach ($students as $studentId) {
                    $registrations[] = [
                        'student_id' => $studentId,
                        'program_id' => $course->program_id,
                        'course_id' => $course->id,
                        'academic_year_id' => $academicYearId,
                        'semester_id' => $semesterId,
                        'attempt_number' => 1,
                        'registration_date' => now()->subDays(rand(10, 30)),
                        'credit_hours' => rand(2, 4),
                        'is_retake' => false,
                        'status' => 'registered',
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insert in chunks to avoid memory issues
            foreach (array_chunk($registrations, 50) as $chunk) {
                DB::table('course_registrations')->insert($chunk);
            }

            $this->command->info('Created ' . count($registrations) . ' course registrations.');
        }

        // Create Course Assessments - tests, quizzes, assignments for each course
        $assessments = [];
        $assessmentNames = [
            'Test 1', 'Test 2', 'Quiz 1', 'Quiz 2', 
            'Assignment 1', 'Assignment 2', 'Midterm Exam',
            'Lab Work', 'Group Project'
        ];

        foreach ($courses as $course) {
            // Create 4-6 assessments per course
            $numAssessments = rand(4, 6);
            $selectedAssessments = array_slice($assessmentNames, 0, $numAssessments);
            
            foreach ($selectedAssessments as $index => $name) {
                $assessmentTypeId = $assessmentTypes[array_rand($assessmentTypes)];
                $maxMarks = in_array($name, ['Midterm Exam']) ? 30 : (in_array($name, ['Group Project', 'Lab Work']) ? 20 : rand(10, 20));
                
                $assessments[] = [
                    'course_id' => $course->id,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'assessment_type_id' => $assessmentTypeId,
                    'title' => $name . ' - ' . DB::table('courses')->where('id', $course->id)->value('code'),
                    'description' => 'Assessment for ' . $name,
                    'max_marks' => $maxMarks,
                    'weight_percentage' => round($maxMarks / 40 * 100, 2), // Weight as percentage of 40% CA
                    'assessment_date' => now()->addDays(rand(-30, 30)),
                    'due_date' => now()->addDays(rand(1, 60)),
                    'status' => 'published',
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('course_assessments')->insert($assessments);
        $this->command->info('Created ' . count($assessments) . ' course assessments.');
        
        $this->command->info('Done! Course registrations and assessments have been created.');
    }
}
