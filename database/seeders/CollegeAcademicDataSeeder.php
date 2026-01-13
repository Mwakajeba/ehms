<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use Carbon\Carbon;

class CollegeAcademicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Academic Years
        $academicYears = [
            [
                'name' => '2024/2025',
                'start_date' => Carbon::create(2024, 9, 1),
                'end_date' => Carbon::create(2025, 8, 31),
                'status' => 'active',
            ],
            [
                'name' => '2025/2026',
                'start_date' => Carbon::create(2025, 9, 1),
                'end_date' => Carbon::create(2026, 8, 31),
                'status' => 'active',
            ],
            [
                'name' => '2026/2027',
                'start_date' => Carbon::create(2026, 9, 1),
                'end_date' => Carbon::create(2027, 8, 31),
                'status' => 'inactive',
            ],
        ];

        foreach ($academicYears as $year) {
            AcademicYear::create($year);
        }

        // Create Semesters
        $semesters = [
            [
                'name' => 'Semester 1',
                'number' => 1,
                'description' => 'First semester',
                'status' => 'active',
            ],
            [
                'name' => 'Semester 2',
                'number' => 2,
                'description' => 'Second semester',
                'status' => 'active',
            ],
            [
                'name' => 'Summer Semester',
                'number' => 3,
                'description' => 'Summer semester/trimester',
                'status' => 'active',
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::create($semester);
        }

        $this->command->info('Academic years and semesters created successfully!');
    }
}
