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
        Schema::create('triage_vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->decimal('temperature', 5, 2)->nullable()->comment('in Celsius');
            $table->integer('blood_pressure_systolic')->nullable();
            $table->integer('blood_pressure_diastolic')->nullable();
            $table->integer('pulse_rate')->nullable()->comment('beats per minute');
            $table->integer('respiratory_rate')->nullable()->comment('breaths per minute');
            $table->decimal('oxygen_saturation', 5, 2)->nullable()->comment('SpO2 percentage');
            $table->decimal('weight', 5, 2)->nullable()->comment('in kg');
            $table->decimal('height', 5, 2)->nullable()->comment('in cm');
            $table->decimal('bmi', 5, 2)->nullable()->comment('Body Mass Index');
            $table->text('chief_complaint')->nullable();
            $table->text('triage_notes')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->foreignId('taken_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['visit_id']);
            $table->index(['patient_id']);
            $table->index(['priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triage_vitals');
    }
};
