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
        Schema::create('assignment_classes', function (Blueprint $table) {
            $table->id();
            
            // Assignment & Class/Stream
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('stream_id')->nullable()->constrained('streams')->onDelete('cascade');
            
            // Extended Due Date (if class-specific extension)
            $table->date('extended_due_date')->nullable();
            $table->time('extended_due_time')->nullable();
            
            // Company & Branch
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            $table->timestamps();
            
            // Unique constraint - one assignment per class-stream combination
            $table->unique(['assignment_id', 'class_id', 'stream_id'], 'unique_assignment_class_stream');
            
            // Indexes
            $table->index(['assignment_id', 'class_id']);
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_classes');
    }
};
