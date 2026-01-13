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
        Schema::create('rch_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_number')->unique();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('inventory_items')->onDelete('set null');
            $table->enum('service_type', [
                'antenatal_care',
                'postnatal_care',
                'child_health',
                'family_planning',
                'immunization',
                'growth_monitoring',
                'health_education',
                'counseling',
                'other'
            ]);
            $table->text('service_description')->nullable();
            $table->text('findings')->nullable()->comment('Clinical findings and observations');
            $table->text('recommendations')->nullable();
            $table->text('counseling_notes')->nullable();
            $table->text('health_education_topics')->nullable();
            $table->text('notes')->nullable();
            $table->json('vitals')->nullable()->comment('Vital signs and measurements (weight, height, BP, etc.)');
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
            $table->index(['service_type']);
            $table->index(['status']);
            $table->index(['next_appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rch_records');
    }
};
