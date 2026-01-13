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
        Schema::create('fee_setting_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_setting_id');
            $table->string('name')->nullable();
            $table->enum('category', ['day', 'boarding']);
            $table->decimal('amount', 15, 2);
            $table->boolean('includes_transport')->default(false);
            $table->timestamps();

            $table->foreign('fee_setting_id')->references('id')->on('fee_settings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_setting_items');
    }
};
