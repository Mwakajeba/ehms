<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\School\Student;
use App\Models\School\Guardian;

class DeleteStudentsAndGuardians extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:delete-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all students and guardians from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete ALL students and guardians? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting deletion process...');

        try {
            DB::beginTransaction();

            // Count records before deletion
            $studentsCount = Student::count();
            $guardiansCount = Guardian::count();
            $pivotCount = DB::table('student_guardians')->count();

            $this->info("Found: {$studentsCount} students, {$guardiansCount} guardians, {$pivotCount} relationships");

            // Step 1: Delete student-guardian relationships (pivot table)
            $this->info('Deleting student-guardian relationships...');
            $deletedPivot = DB::table('student_guardians')->delete();
            $this->info("Deleted {$deletedPivot} relationships");

            // Step 2: Delete fee invoices related to students
            $this->info('Deleting fee invoices...');
            $deletedInvoices = DB::table('fee_invoices')->whereNotNull('student_id')->delete();
            $this->info("Deleted {$deletedInvoices} fee invoices");

            // Step 3: Delete fee invoice items
            $this->info('Deleting fee invoice items...');
            $deletedItems = DB::table('fee_invoice_items')->delete();
            $this->info("Deleted {$deletedItems} fee invoice items");

            // Step 4: Delete students
            $this->info('Deleting students...');
            $deletedStudents = Student::query()->delete();
            $this->info("Deleted {$deletedStudents} students");

            // Step 5: Delete guardians
            $this->info('Deleting guardians...');
            $deletedGuardians = Guardian::query()->delete();
            $this->info("Deleted {$deletedGuardians} guardians");

            DB::commit();

            $this->info('✅ Successfully deleted all students and guardians!');
            $this->info("Summary:");
            $this->info("  - Students: {$deletedStudents}");
            $this->info("  - Guardians: {$deletedGuardians}");
            $this->info("  - Relationships: {$deletedPivot}");
            $this->info("  - Fee Invoices: {$deletedInvoices}");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error occurred during deletion: ' . $e->getMessage());
            $this->error('Transaction rolled back. No data was deleted.');
            return 1;
        }
    }
}
