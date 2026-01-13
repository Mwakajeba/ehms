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
        // Modify the enum to include Term 1 and Term 2
        // MySQL doesn't support ALTER ENUM directly, so we need to use raw SQL
        DB::statement("ALTER TABLE fee_settings MODIFY COLUMN fee_period ENUM('Q1', 'Q2', 'Q3', 'Q4', 'Term 1', 'Term 2', 'Annual') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE fee_settings MODIFY COLUMN fee_period ENUM('Q1', 'Q2', 'Q3', 'Q4', 'Annual') NOT NULL");
    }
};
