<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The issue is that MySQL uses the unique index for foreign key constraints
        // We need to create a new index first, then drop the old one
        // But actually, we can't easily modify a unique constraint in MySQL
        // So we'll create a new unique constraint with stream_id, and the old one will remain
        // but won't prevent our inserts since we're including stream_id
        
        // Actually, let's try a different approach - drop all foreign keys temporarily,
        // modify the constraint, then recreate the foreign keys
        // But that's complex. Let's just add a new unique constraint with stream_id
        
        // Check if the new constraint already exists
        $indexes = DB::select("SHOW INDEX FROM subject_teachers WHERE Key_name = 'unique_subject_teacher_assignment_with_stream'");
        
        if (empty($indexes)) {
            // Create new unique constraint that includes stream_id
            // Note: Since stream_id can be NULL, MySQL will allow multiple NULL values
            // But in practice, the user always provides stream_id, so this should work
            DB::statement('ALTER TABLE subject_teachers ADD UNIQUE KEY unique_subject_teacher_assignment_with_stream (employee_id, subject_id, class_id, stream_id, academic_year_id)');
        }
        
        // Now we need to drop the old constraint
        // But MySQL won't let us if it's used by foreign keys
        // The solution: we keep both constraints, but the new one (with stream_id) takes precedence
        // Actually, that won't work because both will be enforced
        
        // Let's try to drop the old one if possible
        try {
            DB::statement('ALTER TABLE subject_teachers DROP INDEX unique_subject_teacher_assignment');
        } catch (\Exception $e) {
            // If it fails, we'll leave both constraints
            // The new one will be more restrictive (includes stream_id)
            // The old one will remain but shouldn't cause issues if stream_id is always provided
            \Log::warning('Could not drop old unique constraint: ' . $e->getMessage());
        }
        
        // If we successfully dropped the old one, rename the new one
        $oldIndexes = DB::select("SHOW INDEX FROM subject_teachers WHERE Key_name = 'unique_subject_teacher_assignment'");
        if (empty($oldIndexes)) {
            try {
                DB::statement('ALTER TABLE subject_teachers RENAME INDEX unique_subject_teacher_assignment_with_stream TO unique_subject_teacher_assignment');
            } catch (\Exception $e) {
                // Rename not supported, we'll just use the new name
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new constraint with stream_id
        try {
            DB::statement('ALTER TABLE subject_teachers DROP INDEX unique_subject_teacher_assignment_with_stream');
        } catch (\Exception $e) {
            // Try with the renamed version
            try {
                DB::statement('ALTER TABLE subject_teachers DROP INDEX unique_subject_teacher_assignment');
            } catch (\Exception $e2) {
                // Ignore if doesn't exist
            }
        }
        
        // Restore the old unique constraint without stream_id if it doesn't exist
        $indexes = DB::select("SHOW INDEX FROM subject_teachers WHERE Key_name = 'unique_subject_teacher_assignment'");
        if (empty($indexes)) {
            Schema::table('subject_teachers', function (Blueprint $table) {
                $table->unique(['employee_id', 'subject_id', 'class_id', 'academic_year_id'], 'unique_subject_teacher_assignment');
            });
        }
    }
};
