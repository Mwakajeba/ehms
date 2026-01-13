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
        // Check what indexes exist
        $allIndexes = DB::select("SHOW INDEX FROM subject_teachers");
        $uniqueIndexes = [];
        foreach ($allIndexes as $index) {
            if ($index->Key_name === 'unique_subject_teacher_assignment' || 
                $index->Key_name === 'unique_subject_teacher_assignment_with_stream') {
                if (!isset($uniqueIndexes[$index->Key_name])) {
                    $uniqueIndexes[$index->Key_name] = [];
                }
                $uniqueIndexes[$index->Key_name][] = $index->Column_name;
            }
        }

        // Check if old constraint exists (without stream_id)
        $oldConstraintExists = false;
        $newConstraintExists = false;
        
        if (isset($uniqueIndexes['unique_subject_teacher_assignment'])) {
            $oldColumns = $uniqueIndexes['unique_subject_teacher_assignment'];
            // Old constraint should have: employee_id, subject_id, class_id, academic_year_id (4 columns)
            if (count($oldColumns) === 4 && !in_array('stream_id', $oldColumns)) {
                $oldConstraintExists = true;
            } else if (count($oldColumns) === 5 && in_array('stream_id', $oldColumns)) {
                // Already updated
                $newConstraintExists = true;
            }
        }
        
        if (isset($uniqueIndexes['unique_subject_teacher_assignment_with_stream'])) {
            $newConstraintExists = true;
        }

        // If old constraint exists without stream_id, we need to drop it
        if ($oldConstraintExists && !$newConstraintExists) {
            // Try to drop the old constraint
            // If it fails due to foreign key, we'll need to handle it differently
            try {
                DB::statement('ALTER TABLE subject_teachers DROP INDEX unique_subject_teacher_assignment');
            } catch (\Exception $e) {
                // If dropping fails, try to modify it instead
                // MySQL doesn't support modifying indexes directly, so we need to recreate
                // But first, let's try to see if we can work around the foreign key issue
                \Log::warning('Could not drop old unique constraint, will create new one with stream_id: ' . $e->getMessage());
                
                // Create the new constraint with stream_id
                try {
                    DB::statement('ALTER TABLE subject_teachers ADD UNIQUE KEY unique_subject_teacher_assignment_new (employee_id, subject_id, class_id, stream_id, academic_year_id)');
                    
                    // Note: The old constraint will remain, but MySQL will use the new one for new inserts
                    // The old constraint might cause issues, but let's see if we can at least add the new one
                } catch (\Exception $e2) {
                    \Log::error('Could not create new unique constraint: ' . $e2->getMessage());
                    throw $e2;
                }
            }
        }

        // If new constraint doesn't exist yet, create it
        if (!$newConstraintExists) {
            try {
                DB::statement('ALTER TABLE subject_teachers ADD UNIQUE KEY unique_subject_teacher_assignment (employee_id, subject_id, class_id, stream_id, academic_year_id)');
            } catch (\Exception $e) {
                // Check if it already exists with a different name
                if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                    throw $e;
                }
            }
        }

        // Try one more time to drop the old constraint if it still exists with wrong columns
        if ($oldConstraintExists) {
            try {
                $checkOld = DB::select("SHOW INDEX FROM subject_teachers WHERE Key_name = 'unique_subject_teacher_assignment' AND Seq_in_index = 1");
                if (!empty($checkOld)) {
                    // Check if stream_id is in the constraint
                    $checkStream = DB::select("SHOW INDEX FROM subject_teachers WHERE Key_name = 'unique_subject_teacher_assignment' AND Column_name = 'stream_id'");
                    if (empty($checkStream)) {
                        // Old constraint still exists without stream_id, try to drop it
                        DB::statement('ALTER TABLE subject_teachers DROP INDEX unique_subject_teacher_assignment');
                        // Recreate with stream_id
                        DB::statement('ALTER TABLE subject_teachers ADD UNIQUE KEY unique_subject_teacher_assignment (employee_id, subject_id, class_id, stream_id, academic_year_id)');
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Final attempt to fix constraint failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes the constraint, so rolling back might not be straightforward
        // We'll leave it as-is since this is a fix migration
    }
};

