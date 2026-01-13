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
        // Drop the existing foreign key constraint
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        // Update the foreign key to reference inventory_items instead
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('inventory_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the inventory_items foreign key
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        // Restore the original hospital_services foreign key
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('hospital_services')->onDelete('set null');
        });
    }
};
