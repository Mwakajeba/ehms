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
        // Try to drop foreign key - it might have different names
        try {
            Schema::table('fee_settings', function (Blueprint $table) {
                $table->dropForeign(['academic_year_id']);
            });
        } catch (\Exception $e) {
            // Try alternative method - get actual constraint name
            try {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'fee_settings' 
                    AND COLUMN_NAME = 'academic_year_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                    LIMIT 1
                ");
                
                if (!empty($constraints)) {
                    $fkName = $constraints[0]->CONSTRAINT_NAME;
                    DB::statement("ALTER TABLE fee_settings DROP FOREIGN KEY `{$fkName}`");
                }
            } catch (\Exception $e2) {
                \Log::warning('Could not drop foreign key: ' . $e2->getMessage());
            }
        }

        // Drop the unique constraint - try different approaches
        try {
            Schema::table('fee_settings', function (Blueprint $table) {
                $table->dropUnique(['class_id', 'academic_year_id', 'fee_period']);
            });
        } catch (\Exception $e) {
            // Try with raw SQL
            try {
                DB::statement('ALTER TABLE fee_settings DROP INDEX fee_settings_class_id_academic_year_id_fee_period_unique');
            } catch (\Exception $e2) {
                \Log::warning('Could not drop unique constraint: ' . $e2->getMessage());
            }
        }

        // Make academic_year_id nullable
        Schema::table('fee_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->change();
        });

        // Recreate foreign key constraint (now nullable-friendly) - only if it was dropped
        try {
            Schema::table('fee_settings', function (Blueprint $table) {
                $table->foreign('academic_year_id')
                      ->references('id')
                      ->on('academic_years')
                      ->onDelete('set null'); // Changed from 'cascade' to 'set null' since it's now optional
            });
        } catch (\Exception $e) {
            \Log::warning('Could not recreate foreign key: ' . $e->getMessage());
        }

        // Create new unique constraint without academic_year_id
        // This allows fee settings to be reused across academic years
        Schema::table('fee_settings', function (Blueprint $table) {
            // Unique constraint: class + period + company (academic_year_id is optional)
            // Check if constraint already exists
            try {
                $table->unique(['class_id', 'fee_period', 'company_id'], 'fee_settings_unique');
            } catch (\Exception $e) {
                // Constraint might already exist
                \Log::warning('Could not create unique constraint: ' . $e->getMessage());
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_settings', function (Blueprint $table) {
            try {
                $table->dropUnique('fee_settings_unique');
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
        });

        // Drop the nullable foreign key
        try {
            Schema::table('fee_settings', function (Blueprint $table) {
                $table->dropForeign(['academic_year_id']);
            });
        } catch (\Exception $e) {
            // Try to find and drop by name
            try {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'fee_settings' 
                    AND COLUMN_NAME = 'academic_year_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                    LIMIT 1
                ");
                
                if (!empty($constraints)) {
                    $fkName = $constraints[0]->CONSTRAINT_NAME;
                    DB::statement("ALTER TABLE fee_settings DROP FOREIGN KEY `{$fkName}`");
                }
            } catch (\Exception $e2) {
                // Ignore
            }
        }

        // Make academic_year_id not nullable again
        Schema::table('fee_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable(false)->change();
        });

        // Restore the original foreign key and unique constraint
        Schema::table('fee_settings', function (Blueprint $table) {
            $table->foreign('academic_year_id')
                  ->references('id')
                  ->on('academic_years')
                  ->onDelete('cascade');
            
            $table->unique(['class_id', 'academic_year_id', 'fee_period']);
        });
    }
};
