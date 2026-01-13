<?php

namespace Database\Seeders;

use App\Models\School\Subject;
use App\Models\School\SubjectGroup;
use Illuminate\Database\Seeder;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = 1; // Default company
        $branchId = 1;  // Default branch

        // Get subject group IDs
        $scienceGroup = SubjectGroup::where('code', 'SCIENCE')->first();
        $mathGroup = SubjectGroup::where('code', 'MATH')->first();
        $languagesGroup = SubjectGroup::where('code', 'LANGUAGES')->first();
        $humanitiesGroup = SubjectGroup::where('code', 'HUMANITIES')->first();
        $artsGroup = SubjectGroup::where('code', 'ARTS')->first();

        $subjects = [
            [
                'code' => 'ENG101',
                'name' => 'English Language',
                'description' => 'English language and literature studies',
                'type' => 'core',
                'credit_hours' => 3,
                'theory_hours' => 3,
                'practical_hours' => 0,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $languagesGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'MATH101',
                'name' => 'Mathematics',
                'description' => 'Basic mathematics including algebra and geometry',
                'type' => 'core',
                'credit_hours' => 4,
                'theory_hours' => 4,
                'practical_hours' => 0,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $mathGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'PHY101',
                'name' => 'Physics',
                'description' => 'Introduction to physics and physical sciences',
                'type' => 'core',
                'credit_hours' => 3,
                'theory_hours' => 2,
                'practical_hours' => 2,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $scienceGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'CHEM101',
                'name' => 'Chemistry',
                'description' => 'Basic chemistry and chemical reactions',
                'type' => 'core',
                'credit_hours' => 3,
                'theory_hours' => 2,
                'practical_hours' => 2,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $scienceGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'BIO101',
                'name' => 'Biology',
                'description' => 'Introduction to biology and life sciences',
                'type' => 'core',
                'credit_hours' => 3,
                'theory_hours' => 2,
                'practical_hours' => 2,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $scienceGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'HIST101',
                'name' => 'History',
                'description' => 'World and local history studies',
                'type' => 'core',
                'credit_hours' => 2,
                'theory_hours' => 2,
                'practical_hours' => 0,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $humanitiesGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'GEO101',
                'name' => 'Geography',
                'description' => 'Physical and human geography',
                'type' => 'core',
                'credit_hours' => 2,
                'theory_hours' => 2,
                'practical_hours' => 0,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $humanitiesGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'ART101',
                'name' => 'Art and Design',
                'description' => 'Creative arts and design principles',
                'type' => 'elective',
                'credit_hours' => 2,
                'theory_hours' => 1,
                'practical_hours' => 2,
                'passing_marks' => 40.00,
                'total_marks' => 100.00,
                'is_active' => true,
                'subject_group_id' => $artsGroup?->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}