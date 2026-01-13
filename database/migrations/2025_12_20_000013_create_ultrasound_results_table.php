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
        Schema::create('ultrasound_results', function (Blueprint $table) {
            $table->id();
            $table->string('result_number')->unique();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('hospital_services')->onDelete('cascade');
            $table->text('examination_type');
            $table->text('findings')->nullable();
            $table->text('impression')->nullable();
            $table->text('recommendation')->nullable();
            $table->json('images')->nullable()->comment('Array of image file paths');
            $table->enum('result_status', ['pending', 'ready', 'printed', 'delivered'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['visit_id', 'result_status']);
            $table->index(['patient_id']);
            $table->index(['result_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ultrasound_results');
    }
};
