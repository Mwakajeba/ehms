<?php

namespace Database\Seeders;

use App\Models\School\SubjectGroup;
use Illuminate\Database\Seeder;

class SubjectGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = 1; // Default company
        $branchId = 1;  // Default branch

        $subjectGroups = [
            [
                'code' => 'SCIENCE',
                'name' => 'Science Subjects',
                'description' => 'Physics, Chemistry, Biology and related science subjects',
                'category' => 'academic',
                'is_active' => true,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'MATH',
                'name' => 'Mathematics',
                'description' => 'Mathematics and related quantitative subjects',
                'category' => 'academic',
                'is_active' => true,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'LANGUAGES',
                'name' => 'Languages',
                'description' => 'English, Kiswahili and other language subjects',
                'category' => 'academic',
                'is_active' => true,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'HUMANITIES',
                'name' => 'Humanities',
                'description' => 'History, Geography, Religious Studies and social sciences',
                'category' => 'academic',
                'is_active' => true,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            [
                'code' => 'ARTS',
                'name' => 'Arts and Crafts',
                'description' => 'Art, Music, Physical Education and creative subjects',
                'category' => 'academic',
                'is_active' => true,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
        ];

        foreach ($subjectGroups as $group) {
            SubjectGroup::create($group);
        }
    }
}