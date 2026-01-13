<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School\Student;
use App\Models\FeeGroup;
use App\Models\FeeSetting;
use App\Models\School\AcademicYear;
use App\Models\School\Classe;
use App\Services\LipishaService;
use App\Http\Controllers\School\FeeInvoiceController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CreateTestInvoices extends Command
{
    protected $signature = 'invoices:create-test';
    protected $description = 'Create test invoices for students and verify they get LIPISHA bill_number (control number)';

    public function handle()
    {
        $this->info('Creating test invoices for students...');
        
        // Check if LIPISHA is enabled
        if (!LipishaService::isEnabled()) {
            $this->error('❌ LIPISHA integration is disabled. Please enable it in settings.');
            return Command::FAILURE;
        }
        
        // Set authenticated user (required for FeeInvoiceController)
        $user = User::first();
        if (!$user) {
            $this->error('❌ No user found. Please create a user first.');
            return Command::FAILURE;
        }
        Auth::login($user);
        
        // Get students with customer_id
        $students = Student::whereNotNull('lipisha_customer_id')
            ->where('lipisha_customer_id', '!=', '')
            ->where('lipisha_customer_id', '!=', '0')
            ->where('status', 'active')
            ->limit(5)
            ->get();
        
        if ($students->isEmpty()) {
            $this->error('❌ No students with LIPISHA customer_id found.');
            return Command::FAILURE;
        }
        
        $this->info("Found {$students->count()} students with LIPISHA customer_id");
        $this->newLine();
        
        // Get current academic year
        $academicYear = AcademicYear::where('is_current', true)->first();
        if (!$academicYear) {
            $this->error('❌ No current academic year found.');
            return Command::FAILURE;
        }
        
        // Get fee group
        $feeGroup = FeeGroup::first();
        if (!$feeGroup) {
            $this->error('❌ No fee group found. Please create a fee group first.');
            return Command::FAILURE;
        }
        
        // Get fee setting for Q1
        $feeSetting = FeeSetting::where('fee_period', 'Q1')
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->first();
        
        if (!$feeSetting) {
            $this->error('❌ No fee setting found for Q1. Please create fee settings first.');
            return Command::FAILURE;
        }
        
        $this->info("Using Academic Year: {$academicYear->year_name}");
        $this->info("Using Fee Group: {$feeGroup->name}");
        $this->info("Using Fee Setting: {$feeSetting->name}");
        $this->newLine();
        
        $created = 0;
        $withControlNumber = 0;
        $withoutControlNumber = 0;
        $results = [];
        
        foreach ($students as $student) {
            $this->line("Creating invoice for: {$student->first_name} {$student->last_name} (ID: {$student->id})");
            $this->line("  Customer ID: {$student->lipisha_customer_id}");
            
            try {
                // Get class and fee group
                $class = Classe::find($student->class_id);
                if (!$class) {
                    $this->warn("  ⚠️  Class not found for student");
                    continue;
                }
                
                // Ensure student has relationships loaded
                $student->load('class');
                
                // Use FeeInvoiceController method to create invoice
                $controller = new FeeInvoiceController();
                
                // Create invoice using reflection to access private method
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('createInvoiceForStudent');
                $method->setAccessible(true);
                
                try {
                    $result = $method->invoke(
                        $controller,
                        $student,
                        $student->class_id,
                        $academicYear->id,
                        1, // Period 1 (Q1)
                        $feeGroup->id
                    );
                } catch (\Exception $e) {
                    $this->error("  ❌ Exception: " . $e->getMessage());
                    $this->line("  File: " . $e->getFile() . ":" . $e->getLine());
                    continue;
                }
                
                if ($result === true) {
                    $created++;
                    
                    // Check if invoice was created with control number
                    $invoice = \App\Models\FeeInvoice::where('student_id', $student->id)
                        ->where('period', 1)
                        ->where('academic_year_id', $academicYear->id)
                        ->latest()
                        ->first();
                    
                    if ($invoice) {
                        if (!empty($invoice->lipisha_control_number) && 
                            trim($invoice->lipisha_control_number) !== '' && 
                            trim($invoice->lipisha_control_number) !== '-') {
                            $withControlNumber++;
                            $this->info("  ✅ Invoice created with control number: {$invoice->lipisha_control_number}");
                            $results[] = [
                                'student' => $student->first_name . ' ' . $student->last_name,
                                'customer_id' => $student->lipisha_customer_id,
                                'invoice_number' => $invoice->invoice_number,
                                'control_number' => $invoice->lipisha_control_number,
                                'status' => '✅ SUCCESS'
                            ];
                        } else {
                            $withoutControlNumber++;
                            $this->warn("  ⚠️  Invoice created but NO control number");
                            $results[] = [
                                'student' => $student->first_name . ' ' . $student->last_name,
                                'customer_id' => $student->lipisha_customer_id,
                                'invoice_number' => $invoice->invoice_number,
                                'control_number' => $invoice->lipisha_control_number ?? 'NOT_SET',
                                'status' => '⚠️  NO CONTROL NUMBER'
                            ];
                        }
                    } else {
                        $this->warn("  ⚠️  Invoice creation returned true but invoice not found in database");
                    }
                } else {
                    $this->error("  ❌ Failed to create invoice (returned: " . var_export($result, true) . ")");
                    
                    // Check if invoice exists anyway
                    $invoice = \App\Models\FeeInvoice::where('student_id', $student->id)
                        ->where('period', 1)
                        ->where('academic_year_id', $academicYear->id)
                        ->latest()
                        ->first();
                    
                    if ($invoice) {
                        $this->warn("  ⚠️  Invoice exists but method returned false");
                        $created++;
                        
                        if (!empty($invoice->lipisha_control_number) && 
                            trim($invoice->lipisha_control_number) !== '' && 
                            trim($invoice->lipisha_control_number) !== '-') {
                            $withControlNumber++;
                            $this->info("  ✅ Invoice has control number: {$invoice->lipisha_control_number}");
                        } else {
                            $withoutControlNumber++;
                            $this->warn("  ⚠️  Invoice created but NO control number");
                        }
                    }
                }
                
                $this->newLine();
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                $this->error("  Trace: " . $e->getTraceAsString());
                $this->newLine();
            }
        }
        
        // Summary
        $this->newLine();
        $this->info('=== Summary ===');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Invoices Created', $created],
                ['✅ With control number', $withControlNumber],
                ['⚠️  Without control number', $withoutControlNumber],
            ]
        );
        
        if (!empty($results)) {
            $this->newLine();
            $this->info('=== Detailed Results ===');
            $this->table(
                ['Student', 'Customer ID', 'Invoice Number', 'Control Number', 'Status'],
                array_map(function($r) {
                    return [
                        $r['student'],
                        $r['customer_id'],
                        $r['invoice_number'],
                        $r['control_number'],
                        $r['status']
                    ];
                }, $results)
            );
        }
        
        if ($withControlNumber === $created && $created > 0) {
            $this->info("✅ All invoices have LIPISHA control numbers!");
        } else if ($withoutControlNumber > 0) {
            $this->warn("⚠️  {$withoutControlNumber} invoice(s) missing control numbers. Check logs for details.");
        }
        
        return Command::SUCCESS;
    }
}

