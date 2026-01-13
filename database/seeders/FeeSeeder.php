<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School\Student;
use App\Models\School\Fee;
use App\Models\School\FeePayment;
use App\Models\School\AcademicYear;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current academic year
        $currentYear = AcademicYear::current();
        if (!$currentYear) {
            $this->command->info('No current academic year found. Skipping fee seeding.');
            return;
        }

        // Get all students
        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->info('No students found. Skipping fee seeding.');
            return;
        }

        foreach ($students as $student) {
            // Create tuition fee
            $tuitionFee = Fee::create([
                'student_id' => $student->id,
                'academic_year_id' => $currentYear->id,
                'fee_type' => 'tuition',
                'amount' => 25000.00,
                'description' => 'Tuition fee for ' . $currentYear->year_name,
                'due_date' => now()->addDays(30),
                'status' => 'pending',
            ]);

            // Create transport fee if student uses transport
            if ($student->has_transport === 'yes') {
                $transportFee = Fee::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $currentYear->id,
                    'fee_type' => 'transport',
                    'amount' => 8000.00,
                    'description' => 'Transport fee for ' . $currentYear->year_name,
                    'due_date' => now()->addDays(30),
                    'status' => 'pending',
                ]);

                // Add some sample payments for transport fee
                if (rand(0, 1)) { // 50% chance of having payments
                    FeePayment::create([
                        'fee_id' => $transportFee->id,
                        'amount' => 4000.00,
                        'payment_date' => now()->subDays(rand(1, 30)),
                        'payment_method' => 'cash',
                        'reference_number' => 'TRN' . rand(1000, 9999),
                        'notes' => 'Partial payment for transport',
                    ]);
                }
            }

            // Create boarding fee if student is boarding
            if ($student->boarding_type === 'boarding') {
                $boardingFee = Fee::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $currentYear->id,
                    'fee_type' => 'boarding',
                    'amount' => 15000.00,
                    'description' => 'Boarding fee for ' . $currentYear->year_name,
                    'due_date' => now()->addDays(30),
                    'status' => 'pending',
                ]);

                // Add some sample payments for boarding fee
                if (rand(0, 1)) { // 50% chance of having payments
                    FeePayment::create([
                        'fee_id' => $boardingFee->id,
                        'amount' => rand(5000, 10000),
                        'payment_date' => now()->subDays(rand(1, 30)),
                        'payment_method' => 'bank_transfer',
                        'reference_number' => 'BRD' . rand(1000, 9999),
                        'notes' => 'Boarding fee payment',
                    ]);
                }
            }

            // Add some sample payments for tuition fee
            if (rand(0, 2) > 0) { // 66% chance of having payments
                $paymentAmount = rand(5000, 20000);
                FeePayment::create([
                    'fee_id' => $tuitionFee->id,
                    'amount' => $paymentAmount,
                    'payment_date' => now()->subDays(rand(1, 45)),
                    'payment_method' => ['cash', 'bank_transfer', 'mobile_money'][rand(0, 2)],
                    'reference_number' => 'TUT' . rand(1000, 9999),
                    'notes' => 'Tuition fee payment',
                ]);

                // Update fee status based on payment
                if ($paymentAmount >= $tuitionFee->amount) {
                    $tuitionFee->update(['status' => 'paid']);
                } elseif ($paymentAmount > 0) {
                    $tuitionFee->update(['status' => 'partial']);
                }
            }
        }

        $this->command->info('Fee seeding completed successfully.');
    }
}
