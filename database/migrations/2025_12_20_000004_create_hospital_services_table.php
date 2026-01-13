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
        Schema::create('hospital_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', [
                'consultation',
                'lab_test',
                'imaging_test',
                'injection',
                'dressing',
                'vaccine',
                'rch_service',
                'family_planning',
                'dental_service',
                'other'
            ]);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('nhif_eligible')->default(false);
            $table->boolean('chf_eligible')->default(false);
            $table->boolean('jubilee_eligible')->default(false);
            $table->boolean('strategy_eligible')->default(false);
            $table->foreignId('department_id')->nullable()->constrained('hospital_departments')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['type', 'is_active']);
            $table->index(['department_id']);
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_services');
    }
};
