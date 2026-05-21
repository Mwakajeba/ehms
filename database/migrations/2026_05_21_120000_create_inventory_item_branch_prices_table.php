<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_item_branch_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->decimal('unit_price', 15, 2);
            $table->timestamps();

            $table->unique(['item_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_branch_prices');
    }
};
