<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            // Primary School (Levels 1-6)
            ['name' => 'Class 1', 'level' => 1, 'is_active' => true],
            ['name' => 'Class 2', 'level' => 2, 'is_active' => true],
            ['name' => 'Class 3', 'level' => 3, 'is_active' => true],
            ['name' => 'Class 4', 'level' => 4, 'is_active' => true],
            ['name' => 'Class 5', 'level' => 5, 'is_active' => true],
            ['name' => 'Class 6', 'level' => 6, 'is_active' => true],

            // Secondary School (Levels 7-12)
            ['name' => 'Class 7', 'level' => 7, 'is_active' => true],
            ['name' => 'Class 8', 'level' => 8, 'is_active' => true],
            ['name' => 'Class 9', 'level' => 9, 'is_active' => true],
            ['name' => 'Class 10', 'level' => 10, 'is_active' => true],
            ['name' => 'Class 11', 'level' => 11, 'is_active' => true],
            ['name' => 'Class 12', 'level' => 12, 'is_active' => true],
        ];

        // Get default company and branch
        $company = \App\Models\Company::first();
        $branch = \App\Models\Branch::first();

        if ($company && $branch) {
            foreach ($classes as $classData) {
                \App\Models\School\Classe::create([
                    'name' => $classData['name'],
                    'level' => $classData['level'],
                    'is_active' => $classData['is_active'],
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'created_by' => 1, // Assuming admin user exists
                ]);
            }
        }
    }
}
