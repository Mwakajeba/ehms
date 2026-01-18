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
        Schema::create('injection_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_number')->unique();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('inventory_items')->onDelete('set null')->comment('Can be service or product');
            $table->string('injection_type')->comment('e.g., Intramuscular, Intravenous, Subcutaneous, etc.');
            $table->text('injection_description')->nullable();
            $table->text('medication_name')->nullable();
            $table->text('dosage')->nullable();
            $table->text('site')->nullable()->comment('Injection site');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('injection_records');
    }
};
