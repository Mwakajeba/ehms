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
        // Get all indexes
        $allIndexes = DB::select("SHOW INDEX FROM subject_teachers");
        
        $constraints = [];
        foreach ($allIndexes as $index) {
            $keyName = $index->Key_name;
            if ($keyName === 'unique_subject_teacher_assignment' || $keyName === 'unique_subject_teacher_assignment_with_stream') {
                if (!isset($constraints[$keyName])) {
                    $constraints[$keyName] = [];
                }
                $constraints[$keyName][] = $index->Column_name;
            }
        }
        
        // Check if we have the old constraint (without stream_id) or new constraint (with stream_id)
        $hasOldConstraint = false;
        $hasNewConstraint = false;
        
        if (isset($constraints['unique_subject_teacher_assignment'])) {
            $cols = $constraints['unique_subject_teacher_assignment'];
            if (count($cols) === 4 && !in_array('stream_id', $cols)) {
                $hasOldConstraint = true;
            } else if (count($cols) === 5 && in_array('stream_id', $cols)) {
                $hasNewConstraint = true;
            }
        }
        
        if (isset($constraints['unique_subject_teacher_assignment_with_stream'])) {
            $hasNewConstraint = true;
        }
        
        // If we have old constraint without stream_id, we need to remove it and ensure new one exists
        if ($hasOldConstraint && !$hasNewConstraint) {
            // Try to drop the old constraint - if it fails due to foreign key, we'll handle it
            try {
                DB::statement('ALTER TABLE subject_teachers DROP INDEX unique_subject_teacher_assignment');
            } catch (\Exception $e) {
                // If it fails, the constraint might be needed by foreign keys
                // In this case, we'll create the new constraint with a different name
                // and the application will use the new one (which includes stream_id)
                DB::statement('ALTER TABLE subject_teachers ADD UNIQUE KEY unique_subject_teacher_assignment_with_stream (employee_id, subject_id, class_id, stream_id, academic_year_id)');
                \Log::warning('Could not drop old constraint, created new one with different name. Old constraint may cause issues if stream_id is not unique.');
                return;
            }
            
            // Now create the new constraint with stream_id
            DB::statement('ALTER TABLE subject_teachers ADD UNIQUE KEY unique_subject_teacher_assignment (employee_id, subject_id, class_id, stream_id, academic_year_id)');
        } else if ($hasOldConstraint && $hasNewConstraint) {
            // Both exist - we need to drop the old one
            // First, check if the one named 'unique_subject_teacher_assignment' is the old one
            $mainIndexes = DB::select("SHOW INDEX FROM subject_teachers WHERE Key_name = 'unique_subject_teacher_assignment'");
            $hasStreamInMain = false;
            foreach ($mainIndexes as $idx) {
                if ($idx->Column_name === 'stream_id') {
                    $hasStreamInMain = true;
                    break;
                }
            }
            
            if (!$hasStreamInMain) {
                // The main constraint is the old one, drop it
                try {
                    DB::statement('ALTER TABLE subject_teachers DROP INDEX unique_subject_teacher_assignment');
                    // Rename the new one
                    if (isset($constraints['unique_subject_teacher_assignment_with_stream'])) {
                        DB::statement('ALTER TABLE subject_teachers RENAME INDEX unique_subject_teacher_assignment_with_stream TO unique_subject_teacher_assignment');
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not drop old constraint: ' . $e->getMessage());
                }
            }
        } else if (!$hasNewConstraint) {
            // No new constraint exists, create it
            DB::statement('ALTER TABLE subject_teachers ADD UNIQUE KEY unique_subject_teacher_assignment (employee_id, subject_id, class_id, stream_id, academic_year_id)');
        }
        // If hasNewConstraint is true and !hasOldConstraint, we're good - already have the correct constraint
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a fix migration, rolling back might not be safe
        // Leave it as-is
    }
};
