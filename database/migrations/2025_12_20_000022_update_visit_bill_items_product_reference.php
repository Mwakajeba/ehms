<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing foreign key constraint
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // Update the foreign key to reference inventory_items instead
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('inventory_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the inventory_items foreign key
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // Restore the original hospital_products foreign key
        Schema::table('visit_bill_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('hospital_products')->onDelete('set null');
        });
    }
};
