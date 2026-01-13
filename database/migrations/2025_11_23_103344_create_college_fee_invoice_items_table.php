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
        Schema::create('college_fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_fee_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_item_id')->constrained('fee_setting_items')->onDelete('cascade');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index('college_fee_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_fee_invoice_items');
    }
};
