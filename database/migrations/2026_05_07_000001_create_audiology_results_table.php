<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audiology_results', function (Blueprint $table) {
            $table->id();
            $table->string('result_number')->unique();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->text('test_type');
            $table->text('findings')->nullable();
            $table->text('impression')->nullable();
            $table->text('recommendation')->nullable();
            $table->json('attachments')->nullable()->comment('Array of file paths (pdf/images)');
            $table->enum('result_status', ['pending', 'ready', 'printed', 'delivered'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('inventory_items')->onDelete('set null');

            $table->index(['visit_id', 'result_status']);
            $table->index(['patient_id']);
            $table->index(['result_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audiology_results');
    }
};

