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
        Schema::create('fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_invoice_id');
            $table->string('fee_name');
            $table->decimal('amount', 15, 2);
            $table->string('category')->nullable(); // day, boarding, etc.
            $table->boolean('includes_transport')->default(false);
            $table->timestamps();

            $table->foreign('fee_invoice_id')->references('id')->on('fee_invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoice_items');
    }
};
