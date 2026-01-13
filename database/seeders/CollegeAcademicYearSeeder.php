<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\College\AcademicYear;

class CollegeAcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = [
            [
                'name' => '2024/2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-08-31',
                'status' => 'active'
            ],
            [
                'name' => '2025/2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-08-31',
                'status' => 'active'
            ],
            [
                'name' => '2026/2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-08-31',
                'status' => 'active'
            ]
        ];

        foreach ($academicYears as $year) {
            AcademicYear::create($year);
        }
    }
}
