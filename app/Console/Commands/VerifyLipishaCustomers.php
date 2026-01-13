<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School\Student;
use App\Services\LipishaService;
use Illuminate\Support\Facades\Log;

class VerifyLipishaCustomers extends Command
{
    protected $signature = 'lipisha:verify-customers {--limit=10 : Number of customers to verify} {--all : Verify all customers}';
    protected $description = 'Verify LIPISHA customer IDs in database against LIPISHA API';

    public function handle()
    {
        $this->info('Starting LIPISHA customer verification...');
        
        // Check if LIPISHA is enabled
        if (!LipishaService::isEnabled()) {
            $this->error('❌ LIPISHA integration is disabled. Please enable it in settings.');
            return Command::FAILURE;
        }

        // Get students with LIPISHA customer IDs
        $query = Student::whereNotNull('lipisha_customer_id')
            ->where('lipisha_customer_id', '!=', '')
            ->where('lipisha_customer_id', '!=', '0')
            ->where('status', 'active');

        $totalCount = $query->count();
        $this->info("Found {$totalCount} students with LIPISHA customer IDs");

        if ($totalCount === 0) {
            $this->warn('No students with LIPISHA customer IDs found.');
            return Command::SUCCESS;
        }

        // Limit if not --all
        if (!$this->option('all')) {
            $limit = (int) $this->option('limit');
            $query->limit($limit);
        }

        $students = $query->get(['id', 'first_name', 'last_name', 'lipisha_customer_id']);
        
        $this->info("Verifying {$students->count()} customer(s)...");
        $this->newLine();

        $verified = 0;
        $notFound = 0;
        $errors = 0;
        $results = [];

        foreach ($students as $student) {
            $customerId = (int) $student->lipisha_customer_id;
            $studentName = $student->first_name . ' ' . $student->last_name;
            
            $this->line("Verifying Student ID: {$student->id} | Name: {$studentName} | Customer ID: {$customerId}...");
            
            $result = LipishaService::viewCustomer($customerId);
            
            if ($result['success']) {
                $customerData = $result['customer_data'];
                $customerName = $customerData['name'] ?? 'N/A';
                
                $this->info("  ✅ VERIFIED - Customer exists in LIPISHA");
                $this->line("     LIPISHA Customer Name: {$customerName}");
                
                $verified++;
                $results[] = [
                    'student_id' => $student->id,
                    'student_name' => $studentName,
                    'customer_id' => $customerId,
                    'status' => 'VERIFIED',
                    'lipisha_name' => $customerName
                ];
            } else {
                $httpCode = $result['http_code'] ?? null;
                
                if ($httpCode === 404) {
                    $this->error("  ❌ NOT FOUND - Customer ID {$customerId} does not exist in LIPISHA");
                    $notFound++;
                    $results[] = [
                        'student_id' => $student->id,
                        'student_name' => $studentName,
                        'customer_id' => $customerId,
                        'status' => 'NOT_FOUND',
                        'error' => $result['message'] ?? 'Customer not found'
                    ];
                } else {
                    $this->warn("  ⚠️  ERROR - {$result['message']}");
                    $errors++;
                    $results[] = [
                        'student_id' => $student->id,
                        'student_name' => $studentName,
                        'customer_id' => $customerId,
                        'status' => 'ERROR',
                        'error' => $result['message'] ?? 'Unknown error'
                    ];
                }
            }
            
            $this->newLine();
        }

        // Display summary
        $this->newLine();
        $this->info('=== Verification Summary ===');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Verified', $verified],
                ['❌ Not Found', $notFound],
                ['⚠️  Errors', $errors],
                ['Total', $students->count()]
            ]
        );

        // Display detailed results
        if (!empty($results)) {
            $this->newLine();
            $this->info('=== Detailed Results ===');
            $this->table(
                ['Student ID', 'Student Name', 'Customer ID', 'Status', 'Details'],
                array_map(function($r) {
                    return [
                        $r['student_id'],
                        $r['student_name'],
                        $r['customer_id'],
                        $r['status'],
                        $r['lipisha_name'] ?? $r['error'] ?? 'N/A'
                    ];
                }, $results)
            );
        }

        if ($notFound > 0 || $errors > 0) {
            $this->warn("⚠️  Found {$notFound} customer(s) not found in LIPISHA and {$errors} error(s).");
            $this->info("You may need to recreate these customers in LIPISHA.");
        } else {
            $this->info("✅ All verified customers exist in LIPISHA!");
        }

        return Command::SUCCESS;
    }
}

