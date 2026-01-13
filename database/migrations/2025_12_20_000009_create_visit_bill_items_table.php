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
        Schema::create('visit_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('visit_bills')->onDelete('cascade');
            $table->string('item_type')->comment('service or product');
            $table->foreignId('service_id')->nullable()->constrained('hospital_services')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('hospital_products')->onDelete('set null');
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['bill_id']);
            $table->index(['service_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_bill_items');
    }
};
