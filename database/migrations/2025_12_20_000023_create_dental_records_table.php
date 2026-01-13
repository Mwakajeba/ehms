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
        Schema::create('dental_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_number')->unique();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('inventory_items')->onDelete('set null');
            $table->string('procedure_type')->comment('e.g., Cleaning, Filling, Extraction, Root Canal, etc.');
            $table->text('procedure_description')->nullable();
            $table->text('findings')->nullable()->comment('Dental examination findings');
            $table->text('treatment_plan')->nullable();
            $table->text('treatment_performed')->nullable();
            $table->text('notes')->nullable();
            $table->json('images')->nullable()->comment('Array of image file paths');
            $table->enum('status', ['pending', 'completed', 'follow_up_required'])->default('pending');
            $table->date('next_appointment_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['visit_id', 'status']);
            $table->index(['patient_id']);
            $table->index(['status']);
            $table->index(['next_appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_records');
    }
};
