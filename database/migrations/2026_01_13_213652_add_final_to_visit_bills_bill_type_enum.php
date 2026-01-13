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
        // Modify the enum to include 'final'
        DB::statement("ALTER TABLE `visit_bills` MODIFY COLUMN `bill_type` ENUM('pre_bill', 'service_bill', 'pharmacy_bill', 'final') DEFAULT 'pre_bill'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum (remove 'final')
        // Note: This will fail if there are any rows with 'final' value
        DB::statement("ALTER TABLE `visit_bills` MODIFY COLUMN `bill_type` ENUM('pre_bill', 'service_bill', 'pharmacy_bill') DEFAULT 'pre_bill'");
    }
};
