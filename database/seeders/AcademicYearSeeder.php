<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = [
            ['year_name' => '2024-2025'],
            ['year_name' => '2025-2026'],
            ['year_name' => '2026-2027'],
            ['year_name' => '2027-2028'],
            ['year_name' => '2028-2029'],
        ];

        foreach ($academicYears as $year) {
            \App\Models\School\AcademicYear::create($year);
        }
    }
}
