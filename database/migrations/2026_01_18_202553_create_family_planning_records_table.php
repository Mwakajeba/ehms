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
        Schema::create('family_planning_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_number')->unique();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('inventory_items')->onDelete('set null')->comment('Can be service or product');
            $table->string('service_type')->comment('e.g., Counseling, Contraceptive Method, Follow-up, etc.');
            $table->text('method_provided')->nullable()->comment('e.g., Pills, Injectables, Implants, IUD, etc.');
            $table->text('counseling_notes')->nullable();
            $table->date('service_date')->nullable();
            $table->date('next_appointment_date')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('contraindications')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'follow_up_required'])->default('pending');
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
            $table->index(['service_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_planning_records');
    }
};
