<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Company;
use Illuminate\Support\Str;

class HospitalDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found. Please create a company first.');
            return;
        }

        // Define all department types with their details
        $departments = [
            [
                'name' => 'Reception',
                'code' => 'RECEPTION',
                'type' => 'reception',
                'description' => 'Patient registration and visit management',
            ],
            [
                'name' => 'Cashier',
                'code' => 'CASHIER',
                'type' => 'cashier',
                'description' => 'Bill payment and clearance',
            ],
            [
                'name' => 'Triage',
                'code' => 'TRIAGE',
                'type' => 'triage',
                'description' => 'Vital signs recording and patient assessment',
            ],
            [
                'name' => 'Doctor Consultation',
                'code' => 'DOCTOR',
                'type' => 'doctor',
                'description' => 'Medical consultations, diagnosis, and prescriptions',
            ],
            [
                'name' => 'Laboratory',
                'code' => 'LAB',
                'type' => 'lab',
                'description' => 'Laboratory tests and results',
            ],
            [
                'name' => 'Ultrasound',
                'code' => 'ULTRASOUND',
                'type' => 'ultrasound',
                'description' => 'Ultrasound imaging and diagnostics',
            ],
            [
                'name' => 'Dental',
                'code' => 'DENTAL',
                'type' => 'dental',
                'description' => 'Dental procedures and treatments',
            ],
            [
                'name' => 'Pharmacy',
                'code' => 'PHARMACY',
                'type' => 'pharmacy',
                'description' => 'Medication dispensing and prescription management',
            ],
            [
                'name' => 'RCH',
                'code' => 'RCH',
                'type' => 'rch',
                'description' => 'Reproductive and Child Health services',
            ],
            [
                'name' => 'Family Planning',
                'code' => 'FAMILY_PLANNING',
                'type' => 'family_planning',
                'description' => 'Family planning services and counseling',
            ],
            [
                'name' => 'Vaccination',
                'code' => 'VACCINE',
                'type' => 'vaccine',
                'description' => 'Vaccination services',
            ],
            [
                'name' => 'Injection',
                'code' => 'INJECTION',
                'type' => 'injection',
                'description' => 'Injection services',
            ],
            [
                'name' => 'Observation',
                'code' => 'OBSERVATION',
                'type' => 'observation',
                'description' => 'Patient observation and monitoring',
            ],
        ];

        foreach ($companies as $company) {
            $this->command->info("Seeding departments for company: {$company->name}");

            foreach ($departments as $dept) {
                // Check if department already exists
                $existing = HospitalDepartment::where('company_id', $company->id)
                    ->where('code', $dept['code'])
                    ->first();

                if (!$existing) {
                    HospitalDepartment::create([
                        'name' => $dept['name'],
                        'code' => $dept['code'],
                        'type' => $dept['type'],
                        'description' => $dept['description'],
                        'is_active' => true,
                        'company_id' => $company->id,
                        'branch_id' => null, // Company-wide by default
                    ]);

                    $this->command->info("  ✓ Created: {$dept['name']} ({$dept['code']})");
                } else {
                    $this->command->warn("  - Skipped: {$dept['name']} already exists");
                }
            }
        }

        $this->command->info('Hospital departments seeded successfully!');
    }
}
