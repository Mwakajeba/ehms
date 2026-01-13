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
        Schema::create('college_fee_setting_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_fee_setting_id')->constrained('college_fee_settings')->onDelete('cascade');
            $table->foreignId('fee_group_id')->constrained('fee_groups')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->boolean('includes_transport')->default(false);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['college_fee_setting_id', 'sort_order'], 'college_fee_items_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_fee_setting_items');
    }
};
