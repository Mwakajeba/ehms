<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\School\Student;
use App\Services\LipishaService;
use Illuminate\Support\Facades\DB;

class CreateLipishaCustomerForStudent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * Increased to 15 for better reliability with large imports
     */
    public $tries = 15;

    /**
     * The number of seconds the job can run before timing out.
     * Set to 180 seconds (3 minutes) to allow enough time for API calls
     */
    public $timeout = 180;

    /**
     * The number of seconds to wait before retrying the job.
     * Exponential backoff: 10s, 30s, 60s, 120s, 180s, 240s, 300s, 360s, 420s, 480s, 540s, 600s, 660s, 720s, 780s
     * Note: Laravel uses these values for attempts 2-15 (14 values for 14 retries after first attempt)
     * If array has fewer values than retries, last value is reused
     */
    public $backoff = [10, 30, 60, 120, 180, 240, 300, 360, 420, 480, 540, 600, 660, 720, 780];

    /**
     * The maximum number of exceptions before the job is marked as failed.
     */
    public $maxExceptions = 15;

    public $studentId;
    public $phoneNumber;
    public $email;

    /**
     * Create a new job instance.
     */
    public function __construct($studentId, $phoneNumber = null, $email = null)
    {
        $this->studentId = $studentId;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if LIPISHA integration is enabled
        if (!\App\Services\LipishaService::isEnabled()) {
            Log::info('CreateLipishaCustomerForStudent: LIPISHA integration is disabled, skipping job', [
                'student_id' => $this->studentId
            ]);
            return; // Exit early if LIPISHA is disabled
        }

        $attempt = $this->attempts();
        
        try {
            // Get fresh student instance from database
            $student = Student::find($this->studentId);
            
            if (!$student) {
                Log::error('CreateLipishaCustomerForStudent: Student not found', [
                    'student_id' => $this->studentId,
                    'attempt' => $attempt
                ]);
                return; // Don't retry if student doesn't exist
            }

            // Check if student already has customer_id - use database query with lock to avoid race conditions
            // Use transaction to ensure we check and process atomically
            $existingCustomerId = DB::transaction(function () {
                return DB::table('students')
                    ->where('id', $this->studentId)
                    ->lockForUpdate()
                    ->value('lipisha_customer_id');
            }, 5);
            
            // Only skip if customer_id exists AND is valid (not empty, not '0')
            if (!empty($existingCustomerId) && 
                trim($existingCustomerId) !== '' && 
                trim($existingCustomerId) !== '0' &&
                strlen(trim($existingCustomerId)) > 0) {
                Log::info('CreateLipishaCustomerForStudent: Student already has valid customer_id', [
                    'student_id' => $this->studentId,
                    'customer_id' => $existingCustomerId,
                    'attempt' => $attempt,
                    'customer_id_length' => strlen(trim($existingCustomerId))
                ]);
                return; // Success - already has valid customer_id
            }
            
            // Log if we're proceeding to create customer_id
            Log::info('CreateLipishaCustomerForStudent: Student needs customer_id', [
                'student_id' => $this->studentId,
                'existing_customer_id' => $existingCustomerId,
                'is_empty' => empty($existingCustomerId),
                'is_zero' => ($existingCustomerId === '0'),
                'attempt' => $attempt
            ]);

            Log::info('CreateLipishaCustomerForStudent: Starting job', [
                'student_id' => $this->studentId,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'attempt' => $attempt,
                'max_attempts' => $this->tries
            ]);

            // Add delay to avoid rate limiting and race conditions
            // Use student ID to create staggered delays so jobs don't process simultaneously
            // This ensures jobs don't hit API rate limits and reduces race conditions
            $delayMicroseconds = 200000 + (($this->studentId % 10) * 50000); // 200-700ms based on student ID
            usleep($delayMicroseconds);

            // Create customer in LIPISHA with retry logic
            // Service will retry 5 times if it fails
            // CRITICAL: Always try to create, even if check says customer_id exists
            $maxServiceRetries = 5;
            $serviceRetryCount = 0;
            $result = null;
            $dbValue = null;
            $customerIdCreated = null;

            while ($serviceRetryCount < $maxServiceRetries) {
                // Refresh student to get latest data
                $student->refresh();
                
                // Create customer in LIPISHA
                $result = LipishaService::getOrCreateCustomerForStudent(
                    $student,
                    $this->phoneNumber,
                    $this->email
                );

                // Check if we got a valid customer_id
                $customerIdFromResult = $result['customer_id'] ?? null;
                if (!empty($customerIdFromResult) && 
                    trim($customerIdFromResult) !== '' && 
                    trim($customerIdFromResult) !== '0' &&
                    strlen(trim($customerIdFromResult)) > 0) {
                    $customerIdCreated = trim($customerIdFromResult);
                    Log::info('LIPISHA customer created/retrieved successfully in service', [
                        'student_id' => $this->studentId,
                        'customer_id' => $customerIdCreated,
                        'service_retry' => $serviceRetryCount,
                        'result_success' => $result['success'] ?? false
                    ]);
                    break;
                }

                $serviceRetryCount++;
                if ($serviceRetryCount < $maxServiceRetries) {
                    Log::warning('LIPISHA customer creation failed, retrying service call...', [
                        'student_id' => $this->studentId,
                        'retry' => $serviceRetryCount,
                        'max_retries' => $maxServiceRetries,
                        'result' => $result,
                        'customer_id_from_result' => $customerIdFromResult
                    ]);
                    // Exponential backoff: 500ms, 1s, 1.5s, 2s, 2.5s
                    usleep(500000 * $serviceRetryCount);
                } else {
                    Log::error('LIPISHA customer creation failed after all service retries', [
                        'student_id' => $this->studentId,
                        'result' => $result,
                        'customer_id_from_result' => $customerIdFromResult
                    ]);
                }
            }
            
            // If we didn't get customer_id, log error
            if (empty($customerIdCreated)) {
                Log::error('❌ LIPISHA: No customer_id obtained after service retries', [
                    'student_id' => $this->studentId,
                    'service_retries' => $serviceRetryCount,
                    'result' => $result
                ]);
            }

            // Verify the save with retry logic
            // Will verify 5 times to ensure customer_id is saved in database
            // CRITICAL: If we got customer_id from service, ensure it's saved
            $maxVerificationRetries = 5;
            $verificationRetryCount = 0;
            $dbValue = null;

            // If we have customer_id from service, try to save it directly if not in DB
            if (!empty($customerIdCreated)) {
                // Try to save directly if not already saved
                DB::transaction(function () use ($customerIdCreated) {
                    $currentValue = DB::table('students')
                        ->where('id', $this->studentId)
                        ->lockForUpdate()
                        ->value('lipisha_customer_id');
                    
                    if (empty($currentValue) || trim($currentValue) === '' || trim($currentValue) === '0') {
                        DB::table('students')
                            ->where('id', $this->studentId)
                            ->update(['lipisha_customer_id' => $customerIdCreated]);
                        Log::info('LIPISHA: Saved customer_id directly to database', [
                            'student_id' => $this->studentId,
                            'customer_id' => $customerIdCreated
                        ]);
                    }
                }, 5);
            }

            while ($verificationRetryCount < $maxVerificationRetries) {
                // Refresh student model and check database directly
                // Use database lock to prevent race conditions
                $dbValue = DB::transaction(function () {
                    return DB::table('students')
                        ->where('id', $this->studentId)
                        ->lockForUpdate()
                        ->value('lipisha_customer_id');
                }, 5);

                // Check if we have valid customer_id
                if (!empty($dbValue) && 
                    trim($dbValue) !== '' && 
                    trim($dbValue) !== '0' &&
                    strlen(trim($dbValue)) > 0) {
                    $student->refresh();
                    Log::info('✅ Customer ID verified in database', [
                        'student_id' => $this->studentId,
                        'customer_id' => $dbValue,
                        'verification_retry' => $verificationRetryCount,
                        'customer_id_length' => strlen(trim($dbValue))
                    ]);
                    break; // Success - customer_id found
                }

                $verificationRetryCount++;
                if ($verificationRetryCount < $maxVerificationRetries) {
                    // If we have customer_id from service but not in DB, try to save again
                    if (!empty($customerIdCreated)) {
                        DB::table('students')
                            ->where('id', $this->studentId)
                            ->update(['lipisha_customer_id' => $customerIdCreated]);
                    }
                    // Exponential backoff: 200ms, 400ms, 600ms, 800ms, 1s
                    usleep(200000 * $verificationRetryCount);
                }
            }

            if (!empty($dbValue)) {
                Log::info('✅ CreateLipishaCustomerForStudent: SUCCESS', [
                    'student_id' => $this->studentId,
                    'customer_id' => $dbValue,
                    'attempt' => $attempt,
                    'service_retries' => $serviceRetryCount,
                    'verification_retries' => $verificationRetryCount
                ]);
                
                // After successful assignment, check for other students without customer_id
                // and dispatch jobs for them
                $this->checkAndDispatchForOtherStudents($student);
            } else {
                // If still null and we have more attempts, throw exception to retry
                if ($attempt < $this->tries) {
                    Log::warning('❌ CreateLipishaCustomerForStudent: FAILED - will retry', [
                        'student_id' => $this->studentId,
                        'result' => $result,
                        'attempt' => $attempt,
                        'next_attempt' => $attempt + 1,
                        'service_retries' => $serviceRetryCount,
                        'verification_retries' => $verificationRetryCount,
                        'result_success' => $result['success'] ?? false,
                        'result_customer_id' => $result['customer_id'] ?? null
                    ]);
                    throw new \Exception('Customer ID not saved, retrying... Attempt ' . $attempt . ' of ' . $this->tries);
                } else {
                    Log::error('❌ CreateLipishaCustomerForStudent: FAILED - max attempts reached', [
                        'student_id' => $this->studentId,
                        'result' => $result,
                        'attempt' => $attempt,
                        'service_retries' => $serviceRetryCount,
                        'verification_retries' => $verificationRetryCount
                    ]);
                    // Don't throw exception on final attempt - just log and fail silently
                }
            }
        } catch (\Exception $e) {
            Log::error('CreateLipishaCustomerForStudent: Exception', [
                'student_id' => $this->studentId,
                'error' => $e->getMessage(),
                'attempt' => $attempt,
                'max_attempts' => $this->tries,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Only retry if we haven't exceeded max attempts
            if ($attempt < $this->tries) {
                throw $e; // Re-throw to trigger retry
            }
            // Otherwise, fail silently to avoid infinite retries
        }
    }
    
    /**
     * Check for other students without LIPISHA customer_id and dispatch jobs for them
     * This ensures no student is left behind
     */
    private function checkAndDispatchForOtherStudents($currentStudent)
    {
        // Check if LIPISHA integration is enabled
        if (!\App\Services\LipishaService::isEnabled()) {
            Log::info('LIPISHA integration is disabled - skipping check for other students', [
                'current_student_id' => $this->studentId
            ]);
            return;
        }

        try {
            // Find students without customer_id in the same company/branch
            $studentsWithoutCustomerId = Student::where(function($query) {
                    $query->whereNull('lipisha_customer_id')
                          ->orWhere('lipisha_customer_id', '')
                          ->orWhere('lipisha_customer_id', '0');
                })
                ->where('id', '!=', $this->studentId) // Exclude current student
                ->where('company_id', $currentStudent->company_id) // Same company
                ->where(function($query) use ($currentStudent) {
                    // Same branch or no branch filter
                    if ($currentStudent->branch_id) {
                        $query->where('branch_id', $currentStudent->branch_id)
                              ->orWhereNull('branch_id');
                    } else {
                        $query->whereNull('branch_id');
                    }
                })
                ->where('status', 'active') // Only active students
                ->limit(20) // Process 20 at a time to ensure all students get customer_id
                ->get();
            
            if ($studentsWithoutCustomerId->isNotEmpty()) {
                $dispatchedCount = 0;
                
                foreach ($studentsWithoutCustomerId as $student) {
                    // Get phone and email from guardians if available
                    $phoneNumber = null;
                    $email = null;
                    
                    if ($student->guardians && $student->guardians->isNotEmpty()) {
                        $firstGuardian = $student->guardians->first();
                        $phoneNumber = $firstGuardian->phone ?? null;
                        $email = $firstGuardian->email ?? null;
                    }
                    
                    // Dispatch job with small delay to avoid rate limiting
                    $delaySeconds = ($student->id % 5); // 0-4 seconds delay
                    
                    try {
                        self::dispatch(
                            $student->id,
                            $phoneNumber,
                            $email
                        )->onQueue('default')
                         ->delay(now()->addSeconds($delaySeconds));
                        
                        $dispatchedCount++;
                        
                        Log::info('LIPISHA: Dispatched job for student without customer_id', [
                            'student_id' => $student->id,
                            'current_student_id' => $this->studentId,
                            'delay_seconds' => $delaySeconds
                        ]);
                    } catch (\Exception $e) {
                        Log::error('LIPISHA: Failed to dispatch job for student without customer_id', [
                            'student_id' => $student->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                if ($dispatchedCount > 0) {
                    Log::info('✅ LIPISHA: Dispatched jobs for students without customer_id', [
                        'dispatched_count' => $dispatchedCount,
                        'total_found' => $studentsWithoutCustomerId->count(),
                        'current_student_id' => $this->studentId
                    ]);
                }
            } else {
                Log::info('LIPISHA: No other students found without customer_id', [
                    'current_student_id' => $this->studentId,
                    'company_id' => $currentStudent->company_id,
                    'branch_id' => $currentStudent->branch_id
                ]);
            }
        } catch (\Exception $e) {
            // Don't fail the job if checking for other students fails
            Log::error('LIPISHA: Error checking for other students without customer_id', [
                'current_student_id' => $this->studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

