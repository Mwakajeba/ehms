<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School\Student;
use App\Jobs\CreateLipishaCustomerForStudent;
use Illuminate\Support\Facades\Log;

class AddLipishaCustomerIdToExistingStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:add-lipisha-customer-id 
                            {--limit= : Limit number of students to process}
                            {--force : Process even if customer_id exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add LIPISHA customer ID to existing students that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if LIPISHA integration is enabled
        if (!\App\Services\LipishaService::isEnabled()) {
            $this->error('LIPISHA integration is disabled. Please enable it in Settings > LIPISHA Settings.');
            return Command::FAILURE;
        }

        $this->info('Starting to add LIPISHA customer IDs to existing students...');

        // Get students without customer_id - only active students
        $query = Student::where('status', 'active')
            ->where(function($q) {
                $q->whereNull('lipisha_customer_id')
                  ->orWhere('lipisha_customer_id', '')
                  ->orWhere('lipisha_customer_id', '0');
            });

        if ($this->option('force')) {
            $query = Student::query();
        }

        // Count total first
        $total = $query->count();
        
        if ($this->option('limit')) {
            $limit = (int) $this->option('limit');
            $total = min($total, $limit);
            $query->limit($limit);
        }

        if ($total === 0) {
            $this->info('No students found without LIPISHA customer ID.');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} students without LIPISHA customer ID.");
        
        if (!$this->confirm("Do you want to process {$total} students?", true)) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $batchSize = 100; // Process in batches to avoid memory issues

        // Process students in chunks for better performance - use chunk() directly on query
        $query->chunk($batchSize, function ($chunk) use (&$successCount, &$errorCount, &$skippedCount, &$bar) {
            // Eager load guardians for all students in this chunk
            $chunk->load('guardians');
            
            foreach ($chunk as $student) {
                try {
                    // Skip if already has customer_id (unless force)
                    if (!$this->option('force') && !empty($student->lipisha_customer_id)) {
                        $skippedCount++;
                        $bar->advance();
                        continue;
                    }

                    // Get phone number from guardians if available (eager load to avoid N+1)
                    $phoneNumber = null;
                    $email = null;
                    
                    // Guardians already loaded via eager loading in chunk
                    $guardians = $student->guardians;
                    if ($guardians->isNotEmpty()) {
                        $firstGuardian = $guardians->first();
                        $phoneNumber = $firstGuardian->phone ?? null;
                        $email = $firstGuardian->email ?? null;
                    }

                    // Dispatch job to create customer ID with staggered delay
                    // Use student ID to create staggered delays (0-4 seconds)
                    $delaySeconds = ($student->id % 5);
                    
                    CreateLipishaCustomerForStudent::dispatch(
                        $student->id,
                        $phoneNumber,
                        $email
                    )->onQueue('default')
                     ->delay(now()->addSeconds($delaySeconds)); // Staggered delay

                    $successCount++;
                    
                    // Log every 100 students to avoid log spam
                    if ($successCount % 100 === 0) {
                        Log::info('Dispatched LIPISHA customer creation jobs (batch)', [
                            'total_dispatched' => $successCount,
                            'last_student_id' => $student->id
                        ]);
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Error dispatching LIPISHA customer creation job', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Processing complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Jobs Dispatched', $successCount],
                ['Errors', $errorCount],
                ['Skipped', $skippedCount],
                ['Total', $total],
            ]
        );

        $this->info("\nNote: Jobs are being processed in the background.");
        $this->info("Run 'php artisan queue:work' to process the jobs, or they will run automatically if queue is configured.");

        return Command::SUCCESS;
    }
}
