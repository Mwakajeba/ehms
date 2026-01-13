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
        Schema::create('visit_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('hospital_departments')->onDelete('cascade');
            $table->enum('status', ['waiting', 'in_service', 'completed', 'skipped'])->default('waiting');
            $table->timestamp('waiting_started_at')->nullable();
            $table->timestamp('service_started_at')->nullable();
            $table->timestamp('service_ended_at')->nullable();
            $table->integer('waiting_time_seconds')->default(0)->comment('Total waiting time in seconds');
            $table->integer('service_time_seconds')->default(0)->comment('Total service time in seconds');
            $table->foreignId('served_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->integer('sequence')->default(0)->comment('Order of visit to departments');
            $table->timestamps();
            
            // Indexes
            $table->index(['visit_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['status', 'waiting_started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_departments');
    }
};
