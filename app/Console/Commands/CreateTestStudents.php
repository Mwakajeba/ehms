<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\School\AcademicYear;
use App\Models\School\Guardian;
use App\Services\LipishaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateTestStudents extends Command
{
    protected $signature = 'students:create-test {--count=5 : Number of students to create}';
    protected $description = 'Create test students and verify they get LIPISHA customer_id';

    public function handle()
    {
        $count = (int) $this->option('count');
        
        $this->info("Creating {$count} test students...");
        
        // Check if LIPISHA is enabled
        if (!LipishaService::isEnabled()) {
            $this->error('❌ LIPISHA integration is disabled. Please enable it in settings.');
            return Command::FAILURE;
        }
        
        // Get or create a class
        $class = Classe::first();
        if (!$class) {
            $this->error('❌ No class found. Please create a class first.');
            return Command::FAILURE;
        }
        
        // Get current academic year
        $academicYear = AcademicYear::where('is_current', true)->first();
        if (!$academicYear) {
            $this->error('❌ No current academic year found. Please set a current academic year.');
            return Command::FAILURE;
        }
        
        $this->info("Using Class: {$class->name}");
        $this->info("Using Academic Year: {$academicYear->year_name}");
        $this->newLine();
        
        $created = 0;
        $withCustomerId = 0;
        $withoutCustomerId = 0;
        
        for ($i = 1; $i <= $count; $i++) {
            $this->line("Creating student {$i}/{$count}...");
            
            try {
                // Create student
                $student = Student::create([
                    'admission_number' => 'TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'first_name' => 'Test',
                    'last_name' => 'Student ' . $i,
                    'gender' => ($i % 2 == 0) ? 'female' : 'male',
                    'date_of_birth' => now()->subYears(10)->subMonths($i)->format('Y-m-d'),
                    'class_id' => $class->id,
                    'academic_year_id' => $academicYear->id,
                    'status' => 'active',
                    'company_id' => $class->company_id,
                    'branch_id' => $class->branch_id,
                ]);
                
                $this->info("  ✅ Student created: {$student->first_name} {$student->last_name} (ID: {$student->id})");
                
                // Dispatch job to create LIPISHA customer
                \App\Jobs\CreateLipishaCustomerForStudent::dispatch(
                    $student->id,
                    '25570000000' . $i, // Phone number
                    'teststudent' . $i . '@example.com' // Email
                )->onQueue('default');
                
                $this->line("  📤 LIPISHA customer creation job dispatched");
                
                // Wait a bit for job to process
                sleep(2);
                
                // Refresh student to get latest data
                $student->refresh();
                
                // Check if customer_id was assigned
                if (!empty($student->lipisha_customer_id) && 
                    trim($student->lipisha_customer_id) !== '' && 
                    trim($student->lipisha_customer_id) !== '0') {
                    $withCustomerId++;
                    $this->info("  ✅ LIPISHA customer_id: {$student->lipisha_customer_id}");
                } else {
                    $withoutCustomerId++;
                    $this->warn("  ⚠️  LIPISHA customer_id: NOT ASSIGNED YET");
                    
                    // Try to create immediately (synchronous)
                    $this->line("  🔄 Attempting to create customer_id immediately...");
                    $result = LipishaService::getOrCreateCustomerForStudent(
                        $student,
                        '25570000000' . $i,
                        'teststudent' . $i . '@example.com'
                    );
                    
                    $student->refresh();
                    
                    if (!empty($student->lipisha_customer_id) && 
                        trim($student->lipisha_customer_id) !== '' && 
                        trim($student->lipisha_customer_id) !== '0') {
                        $withCustomerId++;
                        $withoutCustomerId--;
                        $this->info("  ✅ LIPISHA customer_id created: {$student->lipisha_customer_id}");
                    } else {
                        $this->error("  ❌ Failed to create customer_id");
                    }
                }
                
                $created++;
                $this->newLine();
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error creating student: " . $e->getMessage());
                $this->newLine();
            }
        }
        
        // Summary
        $this->newLine();
        $this->info('=== Summary ===');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Created', $created],
                ['✅ With customer_id', $withCustomerId],
                ['⚠️  Without customer_id', $withoutCustomerId],
            ]
        );
        
        if ($withCustomerId === $count) {
            $this->info("✅ All students have LIPISHA customer_id!");
        } else {
            $this->warn("⚠️  {$withoutCustomerId} student(s) still missing customer_id. Check queue worker and logs.");
        }
        
        return Command::SUCCESS;
    }
}

