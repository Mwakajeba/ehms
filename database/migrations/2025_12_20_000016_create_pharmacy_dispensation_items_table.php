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
        Schema::create('pharmacy_dispensation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensation_id')->constrained('pharmacy_dispensations')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('hospital_products')->onDelete('cascade');
            $table->integer('quantity_prescribed');
            $table->integer('quantity_dispensed')->default(0);
            $table->text('dosage_instructions')->nullable();
            $table->enum('status', ['pending', 'dispensed', 'partial', 'cancelled'])->default('pending');
            $table->timestamps();
            
            // Indexes
            $table->index(['dispensation_id', 'status']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_dispensation_items');
    }
};
