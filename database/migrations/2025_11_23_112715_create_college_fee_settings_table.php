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
        Schema::create('college_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('college_programs')->onDelete('cascade');
            $table->enum('fee_period', ['Semester 1', 'Semester 2', 'Full year']);
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('category', ['Regular', 'International', 'Special']);
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('includes_transport')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'branch_id']);
            $table->index(['program_id', 'fee_period']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_fee_settings');
    }
};
